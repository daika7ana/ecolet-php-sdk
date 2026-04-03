<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Locations;

final readonly class Country
{
    public function __construct(
        public string $code,
        public string $name,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            code: $data['code'],
            name: $data['name'],
        );
    }
}
