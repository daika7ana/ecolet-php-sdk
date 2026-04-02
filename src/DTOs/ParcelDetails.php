<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs;

/**
 * Parcel details for add-parcel operations.
 */
final readonly class ParcelDetails
{
    /**
     * @param 'package'|'envelope'|'pallet' $type
     * @param 'standard'|'nonstandard'|null $shape
     */
    public function __construct(
        public string $type,
        public ?int $weight = null,
        public ?ParcelDimensions $dimensions = null,
        public ?string $shape = null,
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
            type: $data['type'],
            weight: isset($data['weight']) ? (int) $data['weight'] : null,
            dimensions: isset($data['dimensions']) ? ParcelDimensions::fromArray($data['dimensions']) : null,
            shape: (string) ($data['shape'] ?? null),
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
            'type' => $this->type,
            'amount' => $this->amount,
        ];

        if ($this->weight !== null) {
            $data['weight'] = $this->weight;
        }

        if ($this->dimensions !== null) {
            $data['dimensions'] = $this->dimensions->toArray();
        }

        if ($this->shape !== null) {
            $data['shape'] = $this->shape;
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
