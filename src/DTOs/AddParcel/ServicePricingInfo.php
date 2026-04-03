<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\AddParcel;

/**
 * Service pricing and availability information.
 *
 * Maps courier service slugs to their status/pricing/fees.
 */
final readonly class ServicePricingInfo
{
    /**
     * @param array<string, bool> $statuses Service slug => available
     * @param array<string, mixed> $additionalServices Service slug => available additional services
     * @param array<string, mixed> $pickupDates Service slug => pickup times
     * @param array<string, string|int> $pricesNet Service slug => net price
     * @param array<string, string|int> $pricesGross Service slug => gross price
     * @param array<string, mixed> $fees Service slug => fee breakdown
     * @param array<string, bool> $isStandard Service slug => has standard dimensions
     */
    public function __construct(
        public array $statuses,
        public array $additionalServices,
        public array $pickupDates,
        public array $pricesNet,
        public array $pricesGross,
        public array $fees,
        public array $isStandard,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            statuses: (array) ($data['statuses'] ?? []),
            additionalServices: (array) ($data['additional_services'] ?? []),
            pickupDates: (array) ($data['pickup_dates'] ?? []),
            pricesNet: (array) ($data['prices_net'] ?? []),
            pricesGross: (array) ($data['prices_gross'] ?? []),
            fees: (array) ($data['fees'] ?? []),
            isStandard: (array) ($data['is_standard'] ?? []),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'statuses' => $this->statuses,
            'additional_services' => $this->additionalServices,
            'pickup_dates' => $this->pickupDates,
            'prices_net' => $this->pricesNet,
            'prices_gross' => $this->pricesGross,
            'fees' => $this->fees,
            'is_standard' => $this->isStandard,
        ];
    }
}
