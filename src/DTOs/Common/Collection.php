<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Common;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;
use ArrayAccess;

/**
 * @template TKey of array-key
 * @template TValue
 * @implements Countable
 * @implements IteratorAggregate<TKey, TValue>
 * @implements ArrayAccess<TKey, TValue>
 */
class Collection implements Countable, IteratorAggregate, ArrayAccess
{
    /**
     * @param array<TKey, TValue> $items
     */
    public function __construct(
        protected array $items = [],
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
     * @return static<int, TValue>
     */
    public function values(): static
    {
        return new static(array_values($this->items));
    }

    /**
     * @return static<int, TKey>
     */
    public function keys(): static
    {
        return new static(array_keys($this->items));
    }

    /**
     * @param TKey $key
     */
    public function has(int|string $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * @param TKey $key
     */
    public function hasKey(int|string $key): bool
    {
        return $this->has($key);
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * @param callable(TValue, TKey): bool|null $callback
     * @return static<TKey, TValue>
     */
    public function filter(?callable $callback = null): static
    {
        if ($callback === null) {
            return new static(array_filter($this->items));
        }

        $filteredItems = [];

        foreach ($this->items as $key => $item) {
            if ($callback($item, $key)) {
                $filteredItems[$key] = $item;
            }
        }

        return new static($filteredItems);
    }

    /**
     * @param callable(TValue, TKey): bool $callback
     * @return static<TKey, TValue>
     */
    public function reject(callable $callback): static
    {
        return $this->filter(static fn(mixed $item, int|string $key): bool => !$callback($item, $key));
    }

    /**
     * @param array<array-key, TKey> $keys
     * @return static<TKey, TValue>
     */
    public function only(array $keys): static
    {
        $selectedItems = [];

        foreach ($keys as $key) {
            if (!$this->has($key)) {
                continue;
            }

            $selectedItems[$key] = $this->items[$key];
        }

        return new static($selectedItems);
    }

    /**
     * @param array<array-key, TKey> $keys
     * @return static<TKey, TValue>
     */
    public function except(array $keys): static
    {
        $excludedKeys = array_fill_keys($keys, true);
        $remainingItems = [];

        foreach ($this->items as $key => $item) {
            if (array_key_exists($key, $excludedKeys)) {
                continue;
            }

            $remainingItems[$key] = $item;
        }

        return new static($remainingItems);
    }

    /**
     * @template TMappedValue
     * @param callable(TValue, TKey): TMappedValue $callback
     * @return static<TKey, TMappedValue>
     */
    public function map(callable $callback): static
    {
        $mappedItems = [];

        foreach ($this->items as $key => $item) {
            $mappedItems[$key] = $callback($item, $key);
        }

        return new static($mappedItems);
    }

    /**
     * @template TMappedKey of array-key
     * @template TMappedValue
     * @param callable(TValue, TKey): array<TMappedKey, TMappedValue> $callback
     * @return static<TMappedKey, TMappedValue>
     */
    public function mapWithKeys(callable $callback): static
    {
        $mappedItems = [];

        foreach ($this->items as $key => $item) {
            foreach ($callback($item, $key) as $mappedKey => $mappedValue) {
                $mappedItems[$mappedKey] = $mappedValue;
            }
        }

        return new static($mappedItems);
    }

    /**
     * @param string|int $valueKey
     * @param string|int|null $keyBy
     * @return static<array-key, mixed>
     */
    public function pluck(string|int $valueKey, string|int|null $keyBy = null): static
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

        return new static($pluckedItems);
    }

    /**
     * @param TValue ...$items
     * @return static<TKey, TValue>
     */
    public function push(...$items): static
    {
        foreach ($items as $item) {
            $this->items[] = $item;
        }

        return $this;
    }

    /**
     * @param TValue $item
     * @param TKey|null $key
     * @return static<TKey, TValue>
     */
    public function prepend(mixed $item, mixed $key = null): static
    {
        if ($key === null) {
            array_unshift($this->items, $item);
        } else {
            $this->items = [$key => $item] + $this->items;
        }

        return $this;
    }

    /**
     * @param callable(TValue, TKey): bool|null $callback
     * @return static<TKey, TValue>
     */
    public function each(callable $callback): static
    {
        foreach ($this as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->items;
    }

    public function all(): array
    {
        return $this->items;
    }

    /**
     * @return Traversable<TKey, TValue>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @param TKey $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * @param TKey $offset
     * @return TValue
     */
    public function offsetGet($offset): mixed
    {
        return $this->items[$offset];
    }

    /**
     * @param TKey|null $offset
     * @param TValue $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * @param TKey $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
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
