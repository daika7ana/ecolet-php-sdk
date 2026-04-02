<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs;

final readonly class Locality
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $postalCode = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            postalCode: $data['postal_code'] ?? null,
        );
    }
}
