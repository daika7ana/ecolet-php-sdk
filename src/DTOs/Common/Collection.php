<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Common;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * @template TKey of array-key
 * @template TValue
 * @implements IteratorAggregate<TKey, TValue>
 */
final readonly class Collection implements IteratorAggregate
{
    /**
     * @param array<TKey, TValue> $items
     */
    public function __construct(
        protected array $items,
    ) {}

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return TValue|null
     */
    public function first(): mixed
    {
        $firstKey = array_key_first($this->items);

        if ($firstKey === null) {
            return null;
        }

        return $this->items[$firstKey];
    }

    /**
     * @return TValue|null
     */
    public function last(): mixed
    {
        $lastKey = array_key_last($this->items);

        if ($lastKey === null) {
            return null;
        }

        return $this->items[$lastKey];
    }

    /**
     * @param TKey|null $index
     * @return array<TKey, TValue>|TValue|null
     */
    public function get(int|string|null $index = null): mixed
    {
        if ($index === null) {
            return $this->items;
        }

        return $this->items[$index] ?? null;
    }

    /**
     * @return list<TValue>
     */
    public function values(): array
    {
        return array_values($this->items);
    }

    /**
     * @template TMappedValue
     * @param callable(TValue, TKey): TMappedValue $callback
     * @return self<TKey, TMappedValue>
     */
    public function map(callable $callback): self
    {
        $mappedItems = [];

        foreach ($this->items as $key => $item) {
            $mappedItems[$key] = $callback($item, $key);
        }

        return new self($mappedItems);
    }

    /**
     * @template TMappedKey of array-key
     * @template TMappedValue
     * @param callable(TValue, TKey): array<TMappedKey, TMappedValue> $callback
     * @return self<TMappedKey, TMappedValue>
     */
    public function mapWithKeys(callable $callback): self
    {
        $mappedItems = [];

        foreach ($this->items as $key => $item) {
            foreach ($callback($item, $key) as $mappedKey => $mappedValue) {
                $mappedItems[$mappedKey] = $mappedValue;
            }
        }

        return new self($mappedItems);
    }

    /**
     * @param string|int $valueKey
     * @param string|int|null $keyBy
     * @return self<array-key, mixed>
     */
    public function pluck(string|int $valueKey, string|int|null $keyBy = null): self
    {
        $pluckedItems = [];

        foreach ($this->items as $index => $item) {
            $value = self::pluckValueFromItem($item, $valueKey);

            if ($keyBy === null) {
                $pluckedItems[$index] = $value;

                continue;
            }

            $mappedKey = self::pluckValueFromItem($item, $keyBy);

            if (is_int($mappedKey) || is_string($mappedKey)) {
                $pluckedItems[$mappedKey] = $value;

                continue;
            }

            $pluckedItems[$index] = $value;
        }

        return new self($pluckedItems);
    }

    /**
     * @return Traversable<TKey, TValue>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    private static function pluckValueFromItem(mixed $item, string|int $key): mixed
    {
        if (is_array($item)) {
            return array_key_exists($key, $item) ? $item[$key] : null;
        }

        if (is_object($item) && is_string($key) && property_exists($item, $key)) {
            return $item->{$key};
        }

        return null;
    }
}
