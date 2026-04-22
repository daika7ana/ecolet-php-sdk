<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Locations;

final readonly class Street
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $postalCode = null,
        public ?Locality $locality = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            name: (string) ($data['name'] ?? ''),
            postalCode: array_key_exists('postal_code', $data) && $data['postal_code'] !== null
                ? (string) $data['postal_code']
                : null,
            locality: is_array($data['locality'] ?? null) ? Locality::fromArray($data['locality']) : null,
        );
    }
}
