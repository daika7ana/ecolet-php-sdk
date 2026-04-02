<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\Resources;

use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Config\ClientConfig;
use Daika7ana\Ecolet\Tests\Support\FakeHttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class MapPointsResourceTest extends TestCase
{
    public function testGetMapPointsReturnsTypedResult(): void
    {
        $httpClient = new FakeHttpClient(
            static fn() => new Response(200, [], json_encode([
                'points' => [
                    ['id' => 1, 'name' => 'Point A'],
                ],
            ], JSON_THROW_ON_ERROR)),
        );
        $factory = new HttpFactory();

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: ClientConfig::fromEnvironment(),
        );

        $result = $client->mapPoints()->getMapPoints('RO', ['service_id' => 2]);

        $this->assertCount(1, $result->data['points']);
        $this->assertSame('/api/v1/map-points/RO', $httpClient->lastRequest?->getUri()->getPath());
        $this->assertSame('application/json', $httpClient->lastRequest?->getHeaderLine('Content-Type'));
    }
}
