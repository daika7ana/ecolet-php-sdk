<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Services;

final readonly class ServiceConditions
{
    public function __construct(
        public bool $hasPickupOnlyToday = false,
        public bool $hasMultipacks = false,
        public bool $hasCod = false,
        public bool $hasOpenPackage = false,
        public bool $hasRod = false,
        public bool $hasRop = false,
        public bool $hasSaturdayDelivery = false,
        public bool $hasSmsNotify = false,
        public bool $hasSwap = false,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            hasPickupOnlyToday: (bool) ($data['has_pickup_only_today'] ?? false),
            hasMultipacks: (bool) ($data['has_multipacks'] ?? false),
            hasCod: (bool) ($data['has_cod'] ?? false),
            hasOpenPackage: (bool) ($data['has_open_package'] ?? false),
            hasRod: (bool) ($data['has_rod'] ?? false),
            hasRop: (bool) ($data['has_rop'] ?? false),
            hasSaturdayDelivery: (bool) ($data['has_saturday_delivery'] ?? false),
            hasSmsNotify: (bool) ($data['has_sms_notify'] ?? false),
            hasSwap: (bool) ($data['has_swap'] ?? false),
        );
    }
}
