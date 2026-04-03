<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Services;

final readonly class Service
{
    public function __construct(
        public int $id,
        public string $name,
        public bool $active,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            active: $data['active'] ?? false,
        );
    }
}
