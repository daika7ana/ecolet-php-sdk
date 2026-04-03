<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\MapPoints;

final readonly class MapPointLocality
{
    public function __construct(
        public int $id,
        public string $name,
        public string $municipality,
        public string $postalCode,
        public bool $hasStreets,
        public MapPointCounty $county,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            name: (string) ($data['name'] ?? ''),
            municipality: (string) ($data['municipality'] ?? ''),
            postalCode: (string) ($data['postal_code'] ?? ''),
            hasStreets: (bool) ($data['has_streets'] ?? false),
            county: MapPointCounty::fromArray(is_array($data['county'] ?? null) ? $data['county'] : []),
        );
    }
}
