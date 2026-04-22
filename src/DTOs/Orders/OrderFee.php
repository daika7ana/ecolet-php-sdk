<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Orders;

final readonly class OrderFee
{
    public function __construct(
        public string $type,
        public string $value,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type: (string) ($data['type'] ?? ''),
            value: (string) ($data['value'] ?? ''),
        );
    }
}
