<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Support;

use Daika7ana\Ecolet\Exceptions\UnexpectedStatusException;
use Daika7ana\Ecolet\Exceptions\ValidationException;
use Psr\Http\Message\ResponseInterface;

final class ApiResponseMapper
{
    /**
     * @return array<string, mixed>
     *
     * @throws UnexpectedStatusException
     * @throws ValidationException
     */
    public static function decodeJson(ResponseInterface $response, int $expectedStatus = 200): array
    {
        self::assertStatus($response, $expectedStatus);

        return JsonHelper::decode((string) $response->getBody());
    }

    /**
     * @template T
     *
     * @param callable(array<string, mixed>): T $mapper
     *
     * @return T
     *
     * @throws UnexpectedStatusException
     * @throws ValidationException
     */
    public static function mapJson(ResponseInterface $response, callable $mapper, int $expectedStatus = 200): mixed
    {
        $data = self::decodeJson($response, $expectedStatus);

        return $mapper($data);
    }

    /**
     * @throws UnexpectedStatusException
     * @throws ValidationException
     */
    public static function assertStatus(ResponseInterface $response, int $expectedStatus): void
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode === 422) {
            $payload = self::decodeValidationPayload((string) $response->getBody());

            throw new ValidationException(
                errors: $payload['errors'],
                message: $payload['message'],
                code: 422,
            );
        }

        if ($statusCode !== $expectedStatus) {
            throw new UnexpectedStatusException($response);
        }
    }

    /**
     * @return array{message: string, errors: array<string, string[]>}
     */
    private static function decodeValidationPayload(string $payload): array
    {
        try {
            $decoded = JsonHelper::decode($payload);
        } catch (\Throwable) {
            return [
                'message' => 'Validation failed',
                'errors' => [],
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
        ];
    }
}
