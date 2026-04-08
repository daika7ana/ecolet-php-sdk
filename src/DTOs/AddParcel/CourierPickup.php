<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\AddParcel;

use Daika7ana\Ecolet\Enums\CourierPickupType;

/**
 * Courier pickup configuration.
 */
final readonly class CourierPickup
{
    public function __construct(
        public CourierPickupType $type,
        public ?string $day = null,
        public ?string $date = null,
        public ?string $time = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type: CourierPickupType::tryFrom($data['type'] ?? '') ?? CourierPickupType::Courier,
            day: isset($data['day']) ? (string) $data['day'] : null,
            date: isset($data['date']) ? (string) $data['date'] : null,
            time: isset($data['time']) ? (string) $data['time'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'type' => $this->type->value,
        ];

        if ($this->day !== null) {
            $data['day'] = $this->day;
        }

        if ($this->date !== null) {
            $data['date'] = $this->date;
        }

        if ($this->time !== null) {
            $data['time'] = $this->time;
        }

        return $data;
    }
}
