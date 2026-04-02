<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs;

/**
 * @template T
 */
final readonly class Collection
{
    /**
     * @param array<T> $items
     */
    public function __construct(
        public array $items,
    ) {}

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }
}
