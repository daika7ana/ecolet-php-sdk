<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Services;

final readonly class ServiceCourier
{
    public function __construct(
        public int $id,
        public string $slug,
        public string $name,
        public bool $status = false,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            slug: (string) ($data['slug'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            status: (bool) ($data['status'] ?? false),
        );
    }
}
