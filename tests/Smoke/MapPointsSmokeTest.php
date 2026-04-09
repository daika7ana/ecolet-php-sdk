<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Smoke;

use Daika7ana\Ecolet\DTOs\MapPoints\MapPoint;
use Daika7ana\Ecolet\DTOs\MapPoints\MapPointsResult;
use Daika7ana\Ecolet\Tests\Smoke\Concerns\InteractsWithAuthenticatedSmokeClient;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

final class MapPointsSmokeTest extends TestCase
{
    use InteractsWithAuthenticatedSmokeClient;

    private const CONTEXT = 'map points';
    private const COUNTRY = 'RO';

    #[Group('smoke')]
    public function testGetMapPointsReturnsResult(): void
    {
        $result = $this->fetchMapPoints();

        $this->assertInstanceOf(MapPointsResult::class, $result);
        $this->assertNotEmpty($result->mapPoints->mapPoints);
    }

    #[Group('smoke')]
    public function testGetMapPointsItemsAreProperlyTyped(): void
    {
        $result = $this->fetchMapPoints();

        $this->assertNotEmpty($result->mapPoints->mapPoints);

        $point = $result->mapPoints->mapPoints[0];
        $this->assertInstanceOf(MapPoint::class, $point);
        $this->assertGreaterThan(0, $point->id);
        $this->assertNotSame('', $point->name);
        $this->assertNotSame('', $point->courierSlug);
    }

    #[Group('smoke')]
    public function testGetMapPointsHasBoundingBox(): void
    {
        $result = $this->fetchMapPoints();

        $this->assertNotEmpty($result->mapPoints->boundingBox);
        $this->assertCount(2, $result->mapPoints->boundingBox);
    }

    #[Group('smoke')]
    public function testGetMapPointsWithDestinationFilter(): void
    {
        $client = $this->makeAuthenticatedClient(self::CONTEXT);

        $senderResult = $client->mapPoints()->getMapPoints(self::COUNTRY, ['destination' => 'sender']);
        $receiverResult = $client->mapPoints()->getMapPoints(self::COUNTRY, ['destination' => 'receiver']);

        $this->assertInstanceOf(MapPointsResult::class, $senderResult);
        $this->assertInstanceOf(MapPointsResult::class, $receiverResult);
        $this->assertNotEmpty($receiverResult->mapPoints->mapPoints);
    }

    private function fetchMapPoints(): MapPointsResult
    {
        $client = $this->makeAuthenticatedClient(self::CONTEXT);

        return $client->mapPoints()->getMapPoints(self::COUNTRY);
    }
}
