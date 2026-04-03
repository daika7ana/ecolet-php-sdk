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
    ) {}

    /**
     * Create from a raw array (for API responses or manual construction).
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        // Build parcels list
        $parcelsList = [];
        foreach ($data['parcels'] as $parcelData) {
            $parcelsList[] = ParcelDetails::fromArray($parcelData);
        }

        return new self(
            sender: RecipientAddress::fromArray($data['sender']),
            receiver: RecipientAddress::fromArray($data['receiver']),
            parcel: ParcelDetails::fromArray($data['parcel']),
            additionalServices: AdditionalServices::fromArray($data['additional_services'] ?? []),
            courier: CourierInfo::fromArray($data['courier']),
            parcels: $parcelsList,
            shipmentDetails: isset($data['shipment_details']) ? ShipmentDetails::fromArray($data['shipment_details']) : null,
            coupon: isset($data['coupon']) ? CouponInfo::fromArray($data['coupon']) : null,
        );
    }

    /**
     * Create a simple single-parcel request with typed objects.
     *
     * @param ParcelDetails[] $parcels
     */
    public static function singleParcel(
        RecipientAddress $sender,
        RecipientAddress $receiver,
        ParcelDetails $parcel,
        AdditionalServices $additionalServices,
        CourierInfo $courier,
        array $parcels,
    ): self {
        return new self(
            sender: $sender,
            receiver: $receiver,
            parcel: $parcel,
            additionalServices: $additionalServices,
            courier: $courier,
            parcels: $parcels,
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

        return $data;
    }
}
