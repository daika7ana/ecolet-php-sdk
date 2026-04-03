<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\MapPoints;

final readonly class MapPoint
{
    /**
     * @param list<MapPointCourier> $couriers
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $address,
        public float $lat,
        public float $lng,
        public string $courierSlug,
        public array $couriers,
        public string $type,
        public string $image,
        public bool $isCodAvailable,
        public bool $isForSender,
        public bool $isForReceiver,
        public int $localityId,
        public MapPointOpenHours $openHours,
        public MapPointLocality $locality,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $couriers = [];

        foreach ($data['couriers'] ?? [] as $courier) {
            if (!is_array($courier)) {
                continue;
            }

            $couriers[] = MapPointCourier::fromArray($courier);
        }

        return new self(
            id: (int) ($data['id'] ?? 0),
            name: (string) ($data['name'] ?? ''),
            address: (string) ($data['address'] ?? ''),
            lat: (float) ($data['lat'] ?? 0.0),
            lng: (float) ($data['lng'] ?? 0.0),
            courierSlug: (string) ($data['courier_slug'] ?? ''),
            couriers: $couriers,
            type: (string) ($data['type'] ?? ''),
            image: (string) ($data['image'] ?? ''),
            isCodAvailable: (bool) ($data['is_cod_available'] ?? false),
            isForSender: (bool) ($data['is_for_sender'] ?? false),
            isForReceiver: (bool) ($data['is_for_receiver'] ?? false),
            localityId: (int) ($data['locality_id'] ?? 0),
            openHours: MapPointOpenHours::fromArray(is_array($data['open_hours'] ?? null) ? $data['open_hours'] : []),
            locality: MapPointLocality::fromArray(is_array($data['locality'] ?? null) ? $data['locality'] : []),
        );
    }
}
