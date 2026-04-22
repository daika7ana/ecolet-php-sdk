<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Locations;

use Daika7ana\Ecolet\DTOs\Common\Collection;

final readonly class StreetsByPostalCodeResult
{
    /**
     * @param Collection<int, Street> $streets
     */
    public function __construct(
        public bool $isValid,
        public Collection $streets,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $streetItems = [];

        foreach ($data['streets'] ?? [] as $street) {
            if (!is_array($street)) {
                continue;
            }

            $streetItems[] = Street::fromArray($street);
        }

        return new self(
            isValid: (bool) ($data['is_valid'] ?? false),
            streets: new Collection($streetItems),
        );
    }
}
