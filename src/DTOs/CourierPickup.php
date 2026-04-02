<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs;

/**
 * Courier pickup configuration.
 */
final readonly class CourierPickup
{
    /**
     * @param 'courier'|'self' $type
     */
    public function __construct(
        public string $type,
        public ?string $date = null,
        public ?string $time = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'],
            date: (string) ($data['date'] ?? null),
            time: (string) ($data['time'] ?? null),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'type' => $this->type,
        ];

        if ($this->date !== null) {
            $data['date'] = $this->date;
        }

        if ($this->time !== null) {
            $data['time'] = $this->time;
        }

        return $data;
    }
}
