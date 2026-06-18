<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\AddParcel;

/**
 * Tightly-typed DTO for Add Parcel operations (reload-form, send-order, save-order-to-send).
 *
 * Enforces the schema structure from the Ecolet API, eliminating vague arrays
 * and providing IDE support and strict validation.
 */
final readonly class AddParcelRequest
{
    /**
     * @param list<ParcelDetails> $parcels
     */
    public function __construct(
        public RecipientAddress $sender,
        public RecipientAddress $receiver,
        public ParcelDetails $parcel,
        public AdditionalServices $additionalServices,
        public CourierInfo $courier,
        public array $parcels,
        public ?ShipmentDetails $shipmentDetails = null,
        public ?CouponInfo $coupon = null,
        public ?string $source = null,
    ) {}

    /**
     * Create from a raw array (for API responses or manual construction).
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $payload = is_array($data['data'] ?? null) ? $data['data'] : $data;

        // Build parcels list
        $parcelsList = [];
        foreach (self::arrayOrEmpty($payload['parcels'] ?? null) as $parcelData) {
            if (is_array($parcelData)) {
                $parcelsList[] = ParcelDetails::fromArray($parcelData);
            }
        }

        return new self(
            sender: RecipientAddress::fromArray(self::arrayOrEmpty($payload['sender'] ?? null)),
            receiver: RecipientAddress::fromArray(self::arrayOrEmpty($payload['receiver'] ?? null)),
            parcel: ParcelDetails::fromArray(self::arrayOrEmpty($payload['parcel'] ?? null)),
            additionalServices: AdditionalServices::fromArray(self::arrayOrEmpty($payload['additional_services'] ?? null)),
            courier: CourierInfo::fromArray(self::arrayOrEmpty($payload['courier'] ?? null)),
            parcels: $parcelsList,
            shipmentDetails: is_array($payload['shipment_details'] ?? null) ? ShipmentDetails::fromArray($payload['shipment_details']) : null,
            coupon: is_array($payload['coupon'] ?? null) ? CouponInfo::fromArray($payload['coupon']) : null,
            source: is_string($payload['source'] ?? null) ? $payload['source'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function arrayOrEmpty(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    /**
     * Create a simple single-parcel request with typed objects.
     *
     * @param list<ParcelDetails> $parcels
     */
    public static function singleParcel(
        RecipientAddress $sender,
        RecipientAddress $receiver,
        ParcelDetails $parcel,
        AdditionalServices $additionalServices,
        CourierInfo $courier,
        array $parcels,
        ?string $source = null,
    ): self {
        return new self(
            sender: $sender,
            receiver: $receiver,
            parcel: $parcel,
            additionalServices: $additionalServices,
            courier: $courier,
            parcels: $parcels,
            source: $source,
        );
    }

    /**
     * Convert to API-consumable array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $parcels = [];
        foreach ($this->parcels as $parcel) {
            $parcels[] = $parcel->toArray();
        }

        $data = [
            'sender' => $this->sender->toArray(),
            'receiver' => $this->receiver->toArray(),
            'parcel' => $this->parcel->toArray(),
            'parcels' => $parcels,
            'additional_services' => $this->additionalServices->toArray(),
            'courier' => $this->courier->toArray(),
        ];

        if ($this->shipmentDetails !== null) {
            $data['shipment_details'] = $this->shipmentDetails->toArray();
        }

        if ($this->coupon !== null) {
            $data['coupon'] = $this->coupon->toArray();
        }

        if ($this->source !== null) {
            $data['source'] = $this->source;
        }

        return $data;
    }
}
