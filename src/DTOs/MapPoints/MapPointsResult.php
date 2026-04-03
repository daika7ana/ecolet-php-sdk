<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\MapPoints;

final readonly class MapPointsResult
{
    public function __construct(
        public MapPointsPayload $mapPoints,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            mapPoints: MapPointsPayload::fromArray(is_array($data['mapPoints'] ?? null) ? $data['mapPoints'] : []),
        );
    }
}
