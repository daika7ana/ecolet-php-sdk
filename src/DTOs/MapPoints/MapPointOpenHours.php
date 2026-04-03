<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\MapPoints;

final readonly class MapPointOpenHours
{
    public function __construct(
        public MapPointDaySchedule $monday,
        public MapPointDaySchedule $tuesday,
        public MapPointDaySchedule $wednesday,
        public MapPointDaySchedule $thursday,
        public MapPointDaySchedule $friday,
        public MapPointDaySchedule $saturday,
        public MapPointDaySchedule $sunday,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            monday: MapPointDaySchedule::fromArray(is_array($data['monday'] ?? null) ? $data['monday'] : []),
            tuesday: MapPointDaySchedule::fromArray(is_array($data['tuesday'] ?? null) ? $data['tuesday'] : []),
            wednesday: MapPointDaySchedule::fromArray(is_array($data['wednesday'] ?? null) ? $data['wednesday'] : []),
            thursday: MapPointDaySchedule::fromArray(is_array($data['thursday'] ?? null) ? $data['thursday'] : []),
            friday: MapPointDaySchedule::fromArray(is_array($data['friday'] ?? null) ? $data['friday'] : []),
            saturday: MapPointDaySchedule::fromArray(is_array($data['saturday'] ?? null) ? $data['saturday'] : []),
            sunday: MapPointDaySchedule::fromArray(is_array($data['sunday'] ?? null) ? $data['sunday'] : []),
        );
    }
}
