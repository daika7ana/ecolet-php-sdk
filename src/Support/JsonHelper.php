<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Support;

use JsonException;

final class JsonHelper
{
    /**
     * @throws JsonException
     * @return array<string, mixed>
     */
    public static function decode(string $json): array
    {
        $decoded = json_decode($json, associative: true, flags: JSON_THROW_ON_ERROR);

        if (!is_array($decoded)) {
            throw new JsonException('Expected a JSON object or array.');
        }

        return $decoded;
    }

    /**
     * @throws JsonException
     */
    public static function encode(mixed $value): string
    {
        return json_encode($value, flags: JSON_THROW_ON_ERROR);
    }
}
