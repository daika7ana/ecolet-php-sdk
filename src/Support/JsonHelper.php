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
        return json_decode($json, associative: true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    public static function encode(mixed $value): string
    {
        return json_encode($value, flags: JSON_THROW_ON_ERROR);
    }
}
