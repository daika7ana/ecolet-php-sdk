<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Locations;

final readonly class County
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $code = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            name: (string) ($data['name'] ?? ''),
            code: array_key_exists('code', $data) && $data['code'] !== null ? (string) $data['code'] : null,
        );
    }
}
