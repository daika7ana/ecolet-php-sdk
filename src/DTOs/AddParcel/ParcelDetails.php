<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\AddParcel;

use Daika7ana\Ecolet\Enums\ParcelShape;
use Daika7ana\Ecolet\Enums\ParcelType;

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
        return new self(
            type: ParcelType::tryFrom($data['type'] ?? '') ?? ParcelType::Package,
            weight: isset($data['weight']) ? (int) $data['weight'] : null,
            dimensions: isset($data['dimensions']) ? ParcelDimensions::fromArray($data['dimensions']) : null,
            shape: isset($data['shape']) ? ParcelShape::tryFrom($data['shape']) : null,
            declaredValue: isset($data['declared_value']) ? (float) $data['declared_value'] : null,
            amount: (int) ($data['amount'] ?? 1),
            content: (string) ($data['content'] ?? null),
            observations: (string) ($data['observations'] ?? null),
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
