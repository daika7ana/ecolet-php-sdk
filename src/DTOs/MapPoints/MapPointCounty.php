<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\MapPoints;

final readonly class MapPointCounty
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $code,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            name: (string) ($data['name'] ?? ''),
            code: isset($data['code']) ? (string) $data['code'] : null,
        );
    }
}
