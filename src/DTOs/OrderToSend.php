<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs;

final readonly class OrderToSend
{
    public function __construct(
        public int $id,
        public string $status,
        public ?array $data = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            status: $data['status'],
            data: $data['data'] ?? null,
        );
    }
}
