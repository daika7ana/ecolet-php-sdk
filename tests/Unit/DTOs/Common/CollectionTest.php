<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\DTOs\Common;

use Daika7ana\Ecolet\DTOs\Common\Collection;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    public function testReturnsExpectedHelpersForIndexedCollection(): void
    {
        $collection = new Collection(['first', 'second', 'third']);

        $this->assertSame(3, $collection->count());
        $this->assertSame('first', $collection->first());
        $this->assertSame('third', $collection->last());
        $this->assertSame(['first', 'second', 'third'], $collection->get());
        $this->assertSame('second', $collection->get(1));
        $this->assertSame(['first', 'second', 'third'], $collection->values());
    }

    public function testGetPreservesKeysAndValuesReindexesAssociativeCollections(): void
    {
        $collection = new Collection([
            'alpha' => 'first',
            'omega' => 'last',
        ]);

        $this->assertSame([
            'alpha' => 'first',
            'omega' => 'last',
        ], $collection->get());
        $this->assertSame('first', $collection->first());
        $this->assertSame('last', $collection->last());
        $this->assertSame('last', $collection->get('omega'));
        $this->assertSame(['first', 'last'], $collection->values());
    }

    public function testCollectionIsIterableInForeach(): void
    {
        $collection = new Collection([
            'alpha' => 'first',
            'omega' => 'last',
        ]);

        $iteratedItems = [];

        foreach ($collection as $key => $item) {
            $iteratedItems[$key] = $item;
        }

        $this->assertSame([
            'alpha' => 'first',
            'omega' => 'last',
        ], $iteratedItems);
    }

    public function testMapTransformsEachItemAndPreservesKeys(): void
    {
        $collection = new Collection([
            'alpha' => 2,
            'omega' => 5,
        ]);

        $mapped = $collection->map(static fn(int $value, string $key): string => $key . ':' . ($value * 10));

        $this->assertSame([
            'alpha' => 'alpha:20',
            'omega' => 'omega:50',
        ], $mapped->get());
    }

    public function testMapWithKeysBuildsNewKeyValuePairs(): void
    {
        $collection = new Collection([
            ['id' => 10, 'name' => 'Express'],
            ['id' => 20, 'name' => 'Standard'],
        ]);

        $mapped = $collection->mapWithKeys(static fn(array $item): array => [
            'service_' . $item['id'] => $item['name'],
        ]);

        $this->assertSame([
            'service_10' => 'Express',
            'service_20' => 'Standard',
        ], $mapped->get());
    }

    public function testPluckExtractsValuesFromArraysAndObjects(): void
    {
        $collection = new Collection([
            'first' => ['name' => 'alpha'],
            'second' => (object) ['name' => 'omega'],
            'third' => ['other' => 'missing'],
        ]);

        $plucked = $collection->pluck('name');

        $this->assertSame([
            'first' => 'alpha',
            'second' => 'omega',
            'third' => null,
        ], $plucked->get());
    }

    public function testPluckCanMapValuesByAnotherColumn(): void
    {
        $collection = new Collection([
            ['id' => 10, 'name' => 'Express'],
            (object) ['id' => 20, 'name' => 'Standard'],
            ['id' => null, 'name' => 'Fallback'],
        ]);

        $plucked = $collection->pluck('name', 'id');

        $this->assertSame([
            10 => 'Express',
            20 => 'Standard',
            2 => 'Fallback',
        ], $plucked->get());
    }

    public function testReturnsNullForMissingOrEmptyItems(): void
    {
        $collection = new Collection([]);

        $this->assertNull($collection->first());
        $this->assertNull($collection->last());
        $this->assertNull($collection->get(0));
        $this->assertSame([], $collection->get());
        $this->assertSame([], $collection->values());
    }
}
