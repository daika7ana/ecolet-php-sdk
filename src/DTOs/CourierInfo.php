<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs;

/**
 * Courier information and pickup configuration.
 */
final readonly class CourierInfo
{
    public function __construct(
        public CourierPickup $pickup,
        public ?string $service = null,
        public ?int $contractId = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            pickup: CourierPickup::fromArray($data['pickup']),
            service: (string) ($data['service'] ?? null),
            contractId: isset($data['contract_id']) ? (int) $data['contract_id'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'pickup' => $this->pickup->toArray(),
        ];

        if ($this->service !== null) {
            $data['service'] = $this->service;
        }

        if ($this->contractId !== null) {
            $data['contract_id'] = $this->contractId;
        }

        return $data;
    }
}
