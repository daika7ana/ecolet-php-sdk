<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Support;

use Generator;
use JsonException;
use Psr\Http\Message\StreamInterface;

/**
 * Streams one array out of a JSON document without decoding the whole payload.
 *
 * Only the containers on the path to the target array are supported (nested
 * objects, with the target being an array), which is the shape of responses
 * such as {"mapPoints":{"boundingBox":[...],"mapPoints":[...]}}.
 *
 * When the path is absent or its value is not an array, nothing is yielded;
 * a non-array element inside the array is skipped.
 */
final class JsonStream
{
    private const CHUNK_SIZE = 65536;

    /**
     * Keys, strings and structural characters, used to walk to the target array.
     */
    private const TOKEN_PATTERN = '/"(?:[^"\\\\]++|\\\\.)*+"|"(?:[^"\\\\]++|\\\\.)*+\\\\?\z|[{}\[\]:,]|-?(?:0|[1-9]\d*)(?:\.\d+)?(?:[eE][+-]?\d+)?|true|false|null/s';

    /**
     * One complete object or array, matched with a single anchored regex.
     */
    private const VALUE_PATTERN = '/\{(?:[^{}\[\]"]++|"(?:[^"\\\\]++|\\\\.)*+"|(?R))*\}|\[(?:[^{}\[\]"]++|"(?:[^"\\\\]++|\\\\.)*+"|(?R))*\]/As';

    /**
     * A string, number or literal; used to skip non-object array elements.
     */
    private const SCALAR_PATTERN = '/"(?:[^"\\\\]++|\\\\.)*+"|-?(?:0|[1-9]\d*)(?:\.\d+)?(?:[eE][+-]?\d+)?|true|false|null/As';

    public function __construct(
        private readonly StreamInterface $stream,
        private readonly int $chunkSize = self::CHUNK_SIZE,
    ) {}

    /**
     * Yield the decoded elements of the array found at $path.
     *
     * @param list<string> $path
     *
     * @return Generator<int, array<mixed>>
     *
     * @throws JsonException
     */
    public function arrayAt(array $path): Generator
    {
        if ($path === []) {
            throw new JsonException('JSON path must not be empty.');
        }

        $location = $this->locate($path);

        if ($location === null) {
            return;
        }

        [$buffer, $bufferLength, $pos] = $location;

        while (true) {
            while (true) {
                $pos += strspn($buffer, " \t\r\n,", $pos);

                if ($pos >= $bufferLength) {
                    break;
                }

                if ($buffer[$pos] === ']') {
                    return;
                }

                if ($buffer[$pos] === '{' || $buffer[$pos] === '[') {
                    $result = preg_match(self::VALUE_PATTERN, $buffer, $match, 0, $pos);

                    if ($result === false) {
                        throw new JsonException('Failed to match a JSON element: ' . preg_last_error_msg());
                    }

                    if ($result === 0) {
                        break;
                    }

                    $value = json_decode($match[0], true, flags: JSON_THROW_ON_ERROR);
                    $pos += strlen($match[0]);

                    if (is_array($value)) {
                        yield $value;
                    }
                } else {
                    $result = preg_match(self::SCALAR_PATTERN, $buffer, $match, 0, $pos);

                    if ($result === false) {
                        throw new JsonException('Failed to match a JSON scalar: ' . preg_last_error_msg());
                    }

                    if ($result === 0) {
                        break;
                    }

                    $pos += strlen($match[0]);
                }
            }

            $carry = $pos < $bufferLength ? substr($buffer, $pos) : '';
            $chunk = $this->stream->read($carry === '' ? $this->chunkSize : max($this->chunkSize, strlen($carry)));

            if ($chunk === '') {
                if ($this->stream->eof()) {
                    throw new JsonException('Unexpected end of JSON stream.');
                }

                continue;
            }

            $buffer = $carry . $chunk;
            $bufferLength = strlen($buffer);
            $pos = 0;
        }
    }

    /**
     * Read until the array at $path opens.
     *
     * @param list<string> $path
     *
     * @return array{0: string, 1: int, 2: int}|null Buffer, buffer length and the offset just after "[", or null when the path is missing
     *
     * @throws JsonException
     */
    private function locate(array $path): ?array
    {
        $last = count($path) - 1;
        $carry = '';
        $containers = [];
        $pathIndex = 0;
        $keyDepth = 1;
        $expectKey = false;
        $matchingKey = false;
        $awaitingValue = false;

        while (true) {
            $chunk = $this->stream->read($carry === '' ? $this->chunkSize : max($this->chunkSize, strlen($carry)));

            if ($chunk === '') {
                if ($this->stream->eof()) {
                    throw new JsonException('Unexpected end of JSON stream.');
                }

                continue;
            }

            $buffer = $carry . $chunk;
            $bufferLength = strlen($buffer);
            $carry = '';

            $tokens = $this->tokenize($buffer);
            $ready = count($tokens);
            $safeEnd = $bufferLength;

            if ($ready > 0) {
                $tail = $tokens[$ready - 1];

                if ($tail[0][0] === '"' && !self::isCompleteString($tail[0])) {
                    $safeEnd = $tail[1];
                    $ready--;
                }
            }

            for ($i = 0; $i < $ready; $i++) {
                $token = $tokens[$i][0];
                $offset = $tokens[$i][1];

                if ($token === '{' || $token === '[') {
                    if ($awaitingValue) {
                        $awaitingValue = false;

                        if ($pathIndex === $last) {
                            if ($token !== '[') {
                                return null;
                            }

                            return [$buffer, $bufferLength, $offset + 1];
                        }

                        if ($token !== '{') {
                            return null;
                        }

                        $containers[] = $token;
                        $pathIndex++;
                        $keyDepth = count($containers);
                        $expectKey = true;
                    } else {
                        $containers[] = $token;
                        $expectKey = $token === '{';
                    }
                } elseif ($awaitingValue) {
                    if ($token === '}' || $token === ']' || $token === ',' || $token === ':') {
                        throw new JsonException(sprintf('Expected a JSON value for "%s".', $path[$pathIndex]));
                    }

                    // A scalar value: the path does not lead to an array.
                    return null;
                } elseif ($token === '}' || $token === ']') {
                    array_pop($containers);
                    $depth = count($containers);

                    if ($depth < $keyDepth) {
                        return null;
                    }

                    $expectKey = false;
                } elseif ($token === ':') {
                    if ($matchingKey) {
                        $matchingKey = false;
                        $awaitingValue = true;
                    }

                    $expectKey = false;
                } elseif ($token === ',') {
                    $expectKey = end($containers) === '{';
                } elseif ($token[0] === '"') {
                    // String token: only a key on the path is relevant.
                    if ($expectKey) {
                        $expectKey = false;

                        if (count($containers) === $keyDepth) {
                            $key = json_decode($token, true, flags: JSON_THROW_ON_ERROR);
                            $matchingKey = is_string($key) && $key === $path[$pathIndex];
                        }
                    }
                }
            }

            if ($safeEnd < $bufferLength) {
                $carry = substr($buffer, $safeEnd);
            }
        }
    }

    /**
     * @return list<array{0: string, 1: int}>
     *
     * @throws JsonException
     */
    private function tokenize(string $buffer): array
    {
        $result = preg_match_all(self::TOKEN_PATTERN, $buffer, $matches, PREG_OFFSET_CAPTURE);

        if ($result === false) {
            throw new JsonException('Failed to tokenize the JSON stream.');
        }

        /** @var list<array{0: string, 1: int}> $tokens */
        $tokens = $matches[0];

        return $tokens;
    }

    private static function isCompleteString(string $token): bool
    {
        $length = strlen($token);

        if ($length < 2 || $token[$length - 1] !== '"') {
            return false;
        }

        $backslashes = 0;

        for ($i = $length - 2; $i >= 0 && $token[$i] === '\\'; $i--) {
            $backslashes++;
        }

        return $backslashes % 2 === 0;
    }
}
