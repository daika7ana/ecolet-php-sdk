<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Orders;

final readonly class OrderStatus
{
    public function __construct(
        public int $id,
        public string $status,
        public ?string $description = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            status: $data['status'],
            description: $data['description'] ?? null,
        );
    }
}
