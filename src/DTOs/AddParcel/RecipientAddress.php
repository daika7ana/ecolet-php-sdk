<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\AddParcel;

/**
 * Sender or Receiver address data.
 */
final readonly class RecipientAddress
{
    public function __construct(
        public string $name,
        public string $country,
        public string $locality,
        public int $localityId,
        public string $postalCode,
        public string $streetName,
        public string $streetNumber,
        public string $contactPerson,
        public string $email,
        public string $phone,
        public bool $hasMapPoint = false,
        public ?int $mapPointId = null,
        public ?string $county = null,
        public ?string $block = null,
        public ?string $entrance = null,
        public ?string $floor = null,
        public ?string $flat = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            country: $data['country'],
            locality: $data['locality'],
            localityId: (int) $data['locality_id'],
            postalCode: $data['postal_code'],
            streetName: $data['street_name'],
            streetNumber: $data['street_number'],
            contactPerson: $data['contact_person'],
            email: $data['email'],
            phone: $data['phone'],
            hasMapPoint: (bool) ($data['has_map_point'] ?? false),
            mapPointId: isset($data['map_point_id']) ? (int) $data['map_point_id'] : null,
            county: isset($data['county']) ? (string) $data['county'] : null,
            block: isset($data['block']) ? (string) $data['block'] : null,
            entrance: isset($data['entrance']) ? (string) $data['entrance'] : null,
            floor: isset($data['floor']) ? (string) $data['floor'] : null,
            flat: isset($data['flat']) ? (string) $data['flat'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'country' => $this->country,
            'county' => $this->county,
            'locality' => $this->locality,
            'locality_id' => $this->localityId,
            'postal_code' => $this->postalCode,
            'street_name' => $this->streetName,
            'street_number' => $this->streetNumber,
            'block' => $this->block,
            'entrance' => $this->entrance,
            'floor' => $this->floor,
            'flat' => $this->flat,
            'contact_person' => $this->contactPerson,
            'email' => $this->email,
            'phone' => $this->phone,
            'has_map_point' => $this->hasMapPoint,
            'map_point_id' => $this->mapPointId,
        ];

        return array_filter($data, static fn($v) => $v !== null);
    }
}
