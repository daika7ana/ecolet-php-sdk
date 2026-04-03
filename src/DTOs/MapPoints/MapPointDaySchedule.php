<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\MapPoints;

final readonly class MapPointDaySchedule
{
    public function __construct(
        public string $opened,
        public string $closed,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            opened: (string) ($data['opened'] ?? ''),
            closed: (string) ($data['closed'] ?? ''),
        );
    }
}
