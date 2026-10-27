<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\AddParcel;

use Daika7ana\Ecolet\Enums\ParcelShape;
use Daika7ana\Ecolet\Enums\ParcelType;
use Daika7ana\Ecolet\Exceptions\ValidationException;

/**
 * Parcel details for add-parcel operations.
 */
final readonly class ParcelDetails
{
    /**
     * @param ParcelShape|null $shape
     */
    public function __construct(
        public ParcelType $type,
        public ?int $weight = null,
        public ?ParcelDimensions $dimensions = null,
        public ?ParcelShape $shape = null,
        public ?float $declaredValue = null,
        public int $amount = 1,
        public ?string $content = null,
        public ?string $observations = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $type = (string) ($data['type'] ?? '');
        $parcelType = $type === '' ? ParcelType::Package : ParcelType::tryFrom($type);

        if ($parcelType === null) {
            throw new ValidationException([], sprintf("Unknown parcel type '%s'.", $type));
        }

        return new self(
            type: $parcelType,
            weight: isset($data['weight']) ? (int) $data['weight'] : null,
            dimensions: is_array($data['dimensions'] ?? null) ? ParcelDimensions::fromArray($data['dimensions']) : null,
            shape: isset($data['shape']) ? ParcelShape::tryFrom((string) $data['shape']) : null,
            declaredValue: isset($data['declared_value']) ? (float) $data['declared_value'] : null,
            amount: (int) ($data['amount'] ?? 1),
            content: isset($data['content']) ? (string) $data['content'] : null,
            observations: isset($data['observations']) ? (string) $data['observations'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'type' => $this->type->value,
            'amount' => $this->amount,
        ];

        if ($this->weight !== null) {
            $data['weight'] = $this->weight;
        }

        if ($this->dimensions !== null) {
            $data['dimensions'] = $this->dimensions->toArray();
        }

        if ($this->shape !== null) {
            $data['shape'] = $this->shape->value;
        }

        if ($this->declaredValue !== null) {
            $data['declared_value'] = $this->declaredValue;
        }

        if ($this->content !== null) {
            $data['content'] = $this->content;
        }

        if ($this->observations !== null) {
            $data['observations'] = $this->observations;
        }

        return $data;
    }
}
