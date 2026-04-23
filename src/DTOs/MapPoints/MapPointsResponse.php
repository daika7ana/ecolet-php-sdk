<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\MapPoints;

use Daika7ana\Ecolet\DTOs\Common\Collection;

final readonly class MapPointsResponse
{
    /**
     * @param list<list<float>> $boundingBox
     * @param Collection<int, MapPoint> $mapPoints
     */
    public function __construct(
        public array $boundingBox,
        public Collection $mapPoints,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $boundingBox = [];

        foreach ($data['boundingBox'] ?? [] as $boxPoint) {
            if (!is_array($boxPoint)) {
                continue;
            }

            $boundingBox[] = array_map(
                static fn(mixed $coordinate): float => (float) $coordinate,
                array_values($boxPoint),
            );
        }

        $mapPoints = [];

        foreach ($data['mapPoints'] ?? [] as $mapPoint) {
            if (!is_array($mapPoint)) {
                continue;
            }

            $mapPoints[] = MapPoint::fromArray($mapPoint);
        }

        return new self(
            boundingBox: $boundingBox,
            mapPoints: new Collection($mapPoints),
        );
    }
}
