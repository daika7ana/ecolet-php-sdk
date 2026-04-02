<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs;

/**
 * DTO for Add Parcel form data and send/save operations.
 */
final readonly class AddParcelRequest
{
    /**
     * @param array<string, mixed> $data Request data (varies by operation)
     */
    public function __construct(
        public array $data,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(data: $data);
    }

    /**
     * @param array<string, mixed> $parcel
     */
    public static function singleParcel(array $parcel): self
    {
        return new self(data: ['parcels' => [$parcel]]);
    }

    /**
     * @param array<string, mixed> $basePayload
     * @param array<int, array<string, mixed>> $parcels
     */
    public static function multipack(array $basePayload, array $parcels): self
    {
        $payload = $basePayload;
        $payload['parcels'] = $parcels;

        return new self(data: $payload);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
