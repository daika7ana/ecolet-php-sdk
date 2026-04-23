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
        $this->assertSame(['first', 'second', 'third'], $collection->values()->all());
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
        $this->assertSame(['alpha', 'omega'], $collection->keys()->all());
        $this->assertSame(['first', 'last'], $collection->values()->all());
    }

    public function testStaticReturnMethodsPreserveSubclassInstances(): void
    {
        $collection = new class (['alpha' => 1, 'beta' => 2]) extends Collection {};

        $this->assertInstanceOf($collection::class, $collection->keys());
        $this->assertInstanceOf($collection::class, $collection->values());
        $this->assertInstanceOf($collection::class, $collection->filter(static fn(int $value): bool => $value > 1));
        $this->assertInstanceOf($collection::class, $collection->map(static fn(int $value): int => $value * 10));
        $this->assertInstanceOf($collection::class, $collection->only(['alpha']));
    }

    public function testHasAndHasKeyCheckForExistingKeys(): void
    {
        $collection = new Collection([
            'alpha' => 'first',
            'nullable' => null,
        ]);

        $this->assertTrue($collection->has('alpha'));
        $this->assertTrue($collection->has('nullable'));
        $this->assertTrue($collection->hasKey('alpha'));
        $this->assertFalse($collection->has('missing'));
        $this->assertFalse($collection->hasKey('missing'));
    }

    public function testEmptyStateHelpersReflectCollectionContents(): void
    {
        $emptyCollection = new Collection([]);
        $filledCollection = new Collection(['first']);

        $this->assertTrue($emptyCollection->isEmpty());
        $this->assertFalse($emptyCollection->isNotEmpty());
        $this->assertFalse($filledCollection->isEmpty());
        $this->assertTrue($filledCollection->isNotEmpty());
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

    public function testFilterKeepsMatchingItemsAndPreservesKeys(): void
    {
        $collection = new Collection([
            'alpha' => 1,
            'beta' => 2,
            'gamma' => 3,
        ]);

        $filtered = $collection->filter(static fn(int $value): bool => $value >= 2);

        $this->assertSame([
            'beta' => 2,
            'gamma' => 3,
        ], $filtered->get());
    }

    public function testFilterWithoutCallbackRemovesFalsyValues(): void
    {
        $collection = new Collection([
            'empty-string' => '',
            'zero' => 0,
            'false' => false,
            'kept' => 'value',
        ]);

        $filtered = $collection->filter();

        $this->assertSame([
            'kept' => 'value',
        ], $filtered->get());
    }

    public function testRejectRemovesMatchingItems(): void
    {
        $collection = new Collection([
            'alpha' => 1,
            'beta' => 2,
            'gamma' => 3,
        ]);

        $rejected = $collection->reject(static fn(int $value): bool => $value % 2 === 0);

        $this->assertSame([
            'alpha' => 1,
            'gamma' => 3,
        ], $rejected->get());
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

    public function testOnlyReturnsRequestedKeysInRequestedOrder(): void
    {
        $collection = new Collection([
            'alpha' => 'first',
            'beta' => 'second',
            'gamma' => 'third',
        ]);

        $selected = $collection->only(['gamma', 'alpha', 'missing']);

        $this->assertSame([
            'gamma' => 'third',
            'alpha' => 'first',
        ], $selected->get());
    }

    public function testExceptRemovesRequestedKeys(): void
    {
        $collection = new Collection([
            'alpha' => 'first',
            'beta' => 'second',
            'gamma' => 'third',
        ]);

        $remaining = $collection->except(['beta', 'missing']);

        $this->assertSame([
            'alpha' => 'first',
            'gamma' => 'third',
        ], $remaining->get());
    }

    public function testPushAppendsItemsToTheEndOfTheCollection(): void
    {
        $collection = new Collection(['first']);

        $returnedCollection = $collection->push('second', 'third');

        $this->assertSame($collection, $returnedCollection);
        $this->assertSame(['first', 'second', 'third'], $collection->get());
    }

    public function testPrependAddsItemsToTheBeginningWithAndWithoutExplicitKeys(): void
    {
        $indexedCollection = new Collection(['second', 'third']);
        $associativeCollection = new Collection([
            'beta' => 'second',
            'gamma' => 'third',
        ]);

        $indexedCollection->prepend('first');
        $associativeCollection->prepend('first', 'alpha');

        $this->assertSame(['first', 'second', 'third'], $indexedCollection->get());
        $this->assertSame([
            'alpha' => 'first',
            'beta' => 'second',
            'gamma' => 'third',
        ], $associativeCollection->get());
    }

    public function testEachIteratesItemsUntilTheCallbackStopsExecution(): void
    {
        $collection = new Collection([
            'alpha' => 'first',
            'beta' => 'second',
            'gamma' => 'third',
        ]);

        $visitedItems = [];
        $returnedCollection = $collection->each(static function (string $value, string $key) use (&$visitedItems): bool {
            $visitedItems[$key] = $value;

            return $key !== 'beta';
        });

        $this->assertSame($collection, $returnedCollection);
        $this->assertSame([
            'alpha' => 'first',
            'beta' => 'second',
        ], $visitedItems);
    }

    public function testArrayAccessCanReadWriteAndUnsetItems(): void
    {
        $collection = new Collection([
            'alpha' => 'first',
        ]);

        $this->assertTrue(isset($collection['alpha']));
        $this->assertSame('first', $collection['alpha']);

        $collection['beta'] = 'second';
        $collection[] = 'third';

        $this->assertSame([
            'alpha' => 'first',
            'beta' => 'second',
            0 => 'third',
        ], $collection->get());

        unset($collection['alpha']);

        $this->assertFalse(isset($collection['alpha']));
        $this->assertSame([
            'beta' => 'second',
            0 => 'third',
        ], $collection->get());
    }

    public function testReturnsNullForMissingOrEmptyItems(): void
    {
        $collection = new Collection([]);

        $this->assertNull($collection->first());
        $this->assertNull($collection->last());
        $this->assertNull($collection->get(0));
        $this->assertSame([], $collection->keys()->all());
        $this->assertSame([], $collection->get());
        $this->assertSame([], $collection->values()->all());
    }
}
