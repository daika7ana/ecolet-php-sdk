<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Locations;

final readonly class StreetPostalCode
{
    public function __construct(
        public string $code,
        public ?string $number,
        public ?string $block,
    ) {}

    /**
     * @param array{code: string, number?: ?string, block?: ?string} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            code: $data['code'],
            number: $data['number'] ?? null,
            block: $data['block'] ?? null,
        );
    }
}
