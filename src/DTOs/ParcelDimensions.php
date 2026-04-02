<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs;

/**
 * Parcel dimensions.
 *
 * All measurements in centimeters.
 */
final readonly class ParcelDimensions
{
    public function __construct(
        public int $length,
        public int $width,
        public int $height,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            length: (int) $data['length'],
            width: (int) $data['width'],
            height: (int) $data['height'],
        );
    }

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
        ];
    }
}
