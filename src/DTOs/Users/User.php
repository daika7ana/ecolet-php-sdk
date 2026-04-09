<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Users;

final readonly class User
{
    public function __construct(
        public int $id,
        public string $email,
        public string $name,
        public ?string $phone = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            email: $data['email'],
            name: $data['name'],
            phone: $data['phone'] ?? null,
        );
    }
}
