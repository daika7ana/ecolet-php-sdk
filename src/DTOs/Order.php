<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs;

final readonly class Order
{
    public function __construct(
        public int $id,
        public string $number,
        public ?string $status = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            number: $data['number'],
            status: $data['status'] ?? null,
        );
    }
}
