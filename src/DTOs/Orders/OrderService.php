<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Orders;

final readonly class OrderService
{
    public function __construct(
        public string $slug,
        public string $fullName,
        public string $courierSlug,
        public string $courierName,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            slug: (string) ($data['slug'] ?? ''),
            fullName: (string) ($data['full_name'] ?? ''),
            courierSlug: (string) ($data['courier_slug'] ?? ''),
            courierName: (string) ($data['courier_name'] ?? ''),
        );
    }
}
