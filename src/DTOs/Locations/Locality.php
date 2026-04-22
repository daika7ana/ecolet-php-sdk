<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Locations;

final readonly class Locality
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $postalCode = null,
        public ?string $municipality = null,
        public bool $hasStreets = false,
        public ?County $county = null,
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
            municipality: array_key_exists('municipality', $data) && $data['municipality'] !== null
                ? (string) $data['municipality']
                : null,
            hasStreets: (bool) ($data['has_streets'] ?? false),
            county: is_array($data['county'] ?? null) ? County::fromArray($data['county']) : null,
        );
    }
}
