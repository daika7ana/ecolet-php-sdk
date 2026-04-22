<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Orders;

final readonly class OrderAddress
{
    public function __construct(
        public int $id,
        public string $name,
        public ?int $localityId = null,
        public string $country = '',
        public ?string $county = null,
        public string $locality = '',
        public string $postalCode = '',
        public bool $hasStreets = false,
        public string $streetName = '',
        public string $streetNumber = '',
        public ?string $block = null,
        public ?string $entrance = null,
        public ?string $floor = null,
        public ?string $flat = null,
        public string $contactPerson = '',
        public string $email = '',
        public string $phone = '',
        public ?int $mapPointId = null,
        public ?string $mapPointName = null,
        public ?string $updatedAt = null,
        public ?string $createdAt = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            name: (string) ($data['name'] ?? ''),
            localityId: isset($data['locality_id']) ? (int) $data['locality_id'] : null,
            country: (string) ($data['country'] ?? ''),
            county: isset($data['county']) ? (string) $data['county'] : null,
            locality: (string) ($data['locality'] ?? ''),
            postalCode: (string) ($data['postal_code'] ?? ''),
            hasStreets: (bool) ($data['has_streets'] ?? false),
            streetName: (string) ($data['street_name'] ?? ''),
            streetNumber: (string) ($data['street_number'] ?? ''),
            block: isset($data['block']) ? (string) $data['block'] : null,
            entrance: isset($data['entrance']) ? (string) $data['entrance'] : null,
            floor: isset($data['floor']) ? (string) $data['floor'] : null,
            flat: isset($data['flat']) ? (string) $data['flat'] : null,
            contactPerson: (string) ($data['contact_person'] ?? ''),
            email: (string) ($data['email'] ?? ''),
            phone: (string) ($data['phone'] ?? ''),
            mapPointId: isset($data['map_point_id']) ? (int) $data['map_point_id'] : null,
            mapPointName: isset($data['map_point_name']) ? (string) $data['map_point_name'] : null,
            updatedAt: isset($data['updated_at']) ? (string) $data['updated_at'] : null,
            createdAt: isset($data['created_at']) ? (string) $data['created_at'] : null,
        );
    }
}
