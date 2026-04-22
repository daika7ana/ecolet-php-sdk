<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Services;

final readonly class Service
{
    public function __construct(
        public int $id,
        public string $slug,
        public string $name,
        public ?string $fullName = null,
        public ?string $codeName = null,
        public bool $status = false,
        public bool $active = false,
        public ?string $notes = null,
        public bool $isNew = false,
        public bool $isPromo = false,
        public ?string $logoUrl = null,
        public ?ServiceCourier $courier = null,
        public ?ServiceConditions $conditions = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $status = (bool) ($data['status'] ?? $data['active'] ?? false);

        return new self(
            id: (int) ($data['id'] ?? 0),
            slug: (string) ($data['slug'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            fullName: isset($data['full_name']) ? (string) $data['full_name'] : null,
            codeName: isset($data['code_name']) ? (string) $data['code_name'] : null,
            status: $status,
            active: $status,
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
            isNew: (bool) ($data['is_new'] ?? false),
            isPromo: (bool) ($data['is_promo'] ?? false),
            logoUrl: isset($data['logo_url']) ? (string) $data['logo_url'] : null,
            courier: is_array($data['courier'] ?? null) ? ServiceCourier::fromArray($data['courier']) : null,
            conditions: is_array($data['conditions'] ?? null) ? ServiceConditions::fromArray($data['conditions']) : null,
        );
    }
}
