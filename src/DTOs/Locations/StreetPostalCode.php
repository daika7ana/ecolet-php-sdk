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
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            code: (string) ($data['code'] ?? ''),
            number: isset($data['number']) ? (string) $data['number'] : null,
            block: isset($data['block']) ? (string) $data['block'] : null,
        );
    }
}
