<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Support;

use Daika7ana\Ecolet\DTOs\Common\Errors\EcoletError;
use Daika7ana\Ecolet\Exceptions\AuthenticationException;
use Daika7ana\Ecolet\Exceptions\UnexpectedStatusException;
use Daika7ana\Ecolet\Exceptions\ValidationException;
use JsonException;
use Psr\Http\Message\ResponseInterface;

final class ApiResponseMapper
{
    /**
     * @return array<string, mixed>
     *
     * @throws AuthenticationException
     * @throws UnexpectedStatusException
     * @throws ValidationException
     */
    public static function decodeJson(ResponseInterface $response, int $expectedStatus = 200): array
    {
        self::assertStatus($response, $expectedStatus);

        try {
            return JsonHelper::decode((string) $response->getBody());
        } catch (JsonException $e) {
            throw new UnexpectedStatusException(
                $response,
                'Response body is not valid JSON: ' . $e->getMessage(),
                retryAfter: self::retryAfter($response),
                error: self::errorFromResponse($response),
            );
        }
    }

    /**
     * @template T
     *
     * @param callable(array<string, mixed>): T $mapper
     *
     * @return T
     *
     * @throws AuthenticationException
     * @throws UnexpectedStatusException
     * @throws ValidationException
     */
    public static function mapJson(ResponseInterface $response, callable $mapper, int $expectedStatus = 200): mixed
    {
        $data = self::decodeJson($response, $expectedStatus);

        return $mapper($data);
    }

    /**
     * @return array<string, mixed>
     */
    public static function arrayOrEmpty(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function listOfArrays(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $items = [];

        foreach ($value as $item) {
            if (is_array($item)) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @return list<string>
     */
    public static function listOfStrings(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $items = [];

        foreach ($value as $item) {
            if (is_scalar($item)) {
                $items[] = (string) $item;
            }
        }

        return $items;
    }

    /**
     * @throws AuthenticationException
     * @throws UnexpectedStatusException
     * @throws ValidationException
     */
    public static function assertStatus(ResponseInterface $response, int $expectedStatus): void
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode === $expectedStatus) {
            return;
        }

        if ($statusCode === 401) {
            $error = self::errorFromResponse($response);

            throw new AuthenticationException(
                $error->message,
                response: $response,
                retryAfter: self::retryAfter($response),
                error: $error,
            );
        }

        if ($statusCode === 400 || $statusCode === 422) {
            $payload = self::decodeValidationPayload((string) $response->getBody());

            throw new ValidationException(
                errors: $payload['errors'],
                message: $payload['message'],
                response: $response,
                retryAfter: self::retryAfter($response),
                error: EcoletError::fromArray($payload['raw'], $statusCode),
            );
        }

        throw new UnexpectedStatusException(
            $response,
            retryAfter: self::retryAfter($response),
            error: self::errorFromResponse($response),
        );
    }

    public static function errorFromResponse(ResponseInterface $response): EcoletError
    {
        try {
            $data = JsonHelper::decode((string) $response->getBody());
        } catch (\Throwable) {
            // A malformed body must not hide the status-based exception.
            $data = [];
        }

        return EcoletError::fromArray($data, $response->getStatusCode());
    }

    public static function retryAfter(ResponseInterface $response): ?int
    {
        $value = trim($response->getHeaderLine('Retry-After'));

        return ctype_digit($value) ? (int) $value : null;
    }

    /**
     * @return array{message: string, errors: array<string, string[]>, raw: array<string, mixed>}
     */
    private static function decodeValidationPayload(string $payload): array
    {
        try {
            $decoded = JsonHelper::decode($payload);
        } catch (\Throwable) {
            return [
                'message' => 'Validation failed',
                'errors' => [],
                'raw' => [],
            ];
        }

        $message = 'Validation failed';

        if (isset($decoded['message']) && is_string($decoded['message'])) {
            $message = $decoded['message'];
        } elseif (isset($decoded['error']) && is_string($decoded['error'])) {
            $message = $decoded['error'];
        } elseif (isset($decoded['general_error']) && is_string($decoded['general_error'])) {
            $message = $decoded['general_error'];
        }

        $rawErrors = $decoded['errors'] ?? [];
        $errors = [];

        if (is_array($rawErrors)) {
            foreach ($rawErrors as $field => $messages) {
                if (!is_string($field) || !is_array($messages)) {
                    continue;
                }

                $errors[$field] = array_values(array_filter($messages, static fn(mixed $message): bool => is_string($message)));
            }
        }

        return [
            'message' => $message,
            'errors' => $errors,
            'raw' => $decoded,
        ];
    }
}
