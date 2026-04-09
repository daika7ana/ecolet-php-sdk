<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Support;

final class ResponseFixtureFactory
{
    /**
     * @return array{order_to_send_id: int}
     */
    public static function orderToSendId(int $id): array
    {
        return ['order_to_send_id' => $id];
    }

    /**
     * @param array<string, array<int, string>> $errors
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    public static function reloadForm(array $errors = [], array $overrides = []): array
    {
        $base = [
            'form' => [
                'statuses' => ['dpd_standard' => true],
                'additional_services' => ['dpd_standard' => ['cod' => true]],
                'pickup_dates' => [],
                'prices_net' => ['dpd_standard' => '16.28'],
                'prices_gross' => ['dpd_standard' => '19.37'],
                'fees' => [],
                'is_standard' => ['dpd_standard' => true],
                'billing_weight' => 1,
                'vat' => 19,
                'info' => [],
                'errors' => $errors,
            ],
        ];

        if ($overrides === []) {
            return $base;
        }

        return array_replace_recursive($base, $overrides);
    }

    /**
     * @param array<string, array<int, string>> $errors
     * @return array<string, mixed>
     */
    public static function validationError(array $errors, string $message = 'The given data was invalid.'): array
    {
        return [
            'message' => $message,
            'errors' => $errors,
        ];
    }

    /**
     * @return array{general_error: string}
     */
    public static function generalError(string $message): array
    {
        return ['general_error' => $message];
    }

    /**
     * @return array{message: string}
     */
    public static function serverError(string $message = 'Internal server error.'): array
    {
        return ['message' => $message];
    }
}
