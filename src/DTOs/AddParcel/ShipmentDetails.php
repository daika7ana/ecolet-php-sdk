<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\AddParcel;

/**
 * Optional shipment details for high-value or unusual parcels.
 */
final readonly class ShipmentDetails
{
    public function __construct(
        public ?string $uitCode = null,
        public bool $senderForklift = false,
        public bool $receiverForklift = false,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            uitCode: isset($data['uit_code']) ? (string) $data['uit_code'] : null,
            senderForklift: (bool) ($data['sender_forklift'] ?? false),
            receiverForklift: (bool) ($data['receiver_forklift'] ?? false),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->uitCode !== null) {
            $data['uit_code'] = $this->uitCode;
        }

        if ($this->senderForklift) {
            $data['sender_forklift'] = true;
        }

        if ($this->receiverForklift) {
            $data['receiver_forklift'] = true;
        }

        return $data;
    }
}
