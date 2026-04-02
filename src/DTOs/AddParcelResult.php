<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs;

final readonly class AddParcelResult
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        public array $data,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}
