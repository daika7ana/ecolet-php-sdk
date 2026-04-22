<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Locations;

final readonly class Country
{
    public function __construct(
        public string $code,
        public string $name,
        public int $id = 0,
        public bool $isDefault = false,
        public bool $hasCounties = false,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            code: (string) ($data['code'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            id: (int) ($data['id'] ?? 0),
            isDefault: (bool) ($data['is_default'] ?? false),
            hasCounties: (bool) ($data['has_counties'] ?? false),
        );
    }
}
