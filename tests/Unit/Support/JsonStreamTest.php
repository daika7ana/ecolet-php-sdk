<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\Support;

use Daika7ana\Ecolet\Support\JsonStream;
use GuzzleHttp\Psr7\Utils;
use JsonException;
use PHPUnit\Framework\TestCase;

class JsonStreamTest extends TestCase
{
    public function testYieldsElementsAtPathAcrossChunkBoundaries(): void
    {
        $payload = '{"outer":{"ignored":{"wanted":"no"},"wanted":['
            . '{"name":"brace { } [ ] , : inside"},'
            . '{"name":"escaped \\" quote and \\\\ backslash"},'
            . '{"nested":{"deep":[1,2,3]},"unicode":"\\u007d"}'
            . ']}}';

        $stream = new JsonStream(Utils::streamFor($payload), chunkSize: 3);
        $elements = iterator_to_array($stream->arrayAt(['outer', 'wanted']));

        $this->assertCount(3, $elements);
        $this->assertSame('brace { } [ ] , : inside', $elements[0]['name']);
        $this->assertSame('escaped " quote and \\ backslash', $elements[1]['name']);
        $this->assertSame(['deep' => [1, 2, 3]], $elements[2]['nested']);
        $this->assertSame('}', $elements[2]['unicode']);
    }

    public function testElementSpanningChunksIsYieldedOnce(): void
    {
        $name = str_repeat('x', 200);
        $payload = '{"a":{"b":[{"name":"' . $name . '"}]}}';

        $stream = new JsonStream(Utils::streamFor($payload), chunkSize: 16);
        $elements = iterator_to_array($stream->arrayAt(['a', 'b']));

        $this->assertCount(1, $elements);
        $this->assertSame($name, $elements[0]['name']);
    }

    public function testLargeElementIsYielded(): void
    {
        $name = str_repeat('x', 100_000);
        $payload = '{"a":{"b":[{"name":"' . $name . '"}]}}';

        $stream = new JsonStream(Utils::streamFor($payload), chunkSize: 16);
        $elements = iterator_to_array($stream->arrayAt(['a', 'b']));

        $this->assertCount(1, $elements);
        $this->assertSame($name, $elements[0]['name']);
    }

    public function testEmptyArrayYieldsNothing(): void
    {
        $stream = new JsonStream(Utils::streamFor('{"mapPoints":{"mapPoints":[]}}'));

        $this->assertCount(0, iterator_to_array($stream->arrayAt(['mapPoints', 'mapPoints'])));
    }

    public function testMissingPathYieldsNothing(): void
    {
        $stream = new JsonStream(Utils::streamFor('{"mapPoints":{"boundingBox":[]}}'));

        $this->assertCount(0, iterator_to_array($stream->arrayAt(['mapPoints', 'mapPoints'])));
    }

    public function testNonArrayValueYieldsNothing(): void
    {
        $stream = new JsonStream(Utils::streamFor('{"mapPoints":{"boundingBox":[],"mapPoints":null}}'));

        $this->assertCount(0, iterator_to_array($stream->arrayAt(['mapPoints', 'mapPoints'])));
    }

    public function testMalformedMissingValueThrows(): void
    {
        $stream = new JsonStream(Utils::streamFor('{"mapPoints":{"mapPoints":}}'));

        $this->expectException(JsonException::class);
        $this->expectExceptionMessage('Expected a JSON value for "mapPoints"');

        iterator_to_array($stream->arrayAt(['mapPoints', 'mapPoints']));
    }

    public function testScalarElementsAreSkipped(): void
    {
        $stream = new JsonStream(Utils::streamFor('{"a":{"b":["x",1,true,null,{"id":9}]}}'));

        $this->assertSame([['id' => 9]], iterator_to_array($stream->arrayAt(['a', 'b'])));
    }

    public function testTruncatedStreamThrows(): void
    {
        $stream = new JsonStream(Utils::streamFor('{"a":{"b":[{"id":1}'));

        $this->expectException(JsonException::class);
        $this->expectExceptionMessage('Unexpected end of JSON stream');

        iterator_to_array($stream->arrayAt(['a', 'b']));
    }
}
