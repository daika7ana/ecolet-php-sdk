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
                'mapPoints' => [
                    'boundingBox' => [
                        [47.950271263635, 29.22991],
                        [43.786041, 20.301205990759],
                    ],
                    'mapPoints' => [[
                        'id' => 1,
                        'name' => 'Point A',
                        'address' => 'Main St. 1',
                        'lat' => 46.5671,
                        'lng' => 26.911153,
                        'courier_slug' => 'sameday',
                        'couriers' => [[
                            'id' => 2,
                            'slug' => 'sameday',
                            'name' => 'Sameday',
                            'status' => true,
                        ]],
                        'type' => 'locker',
                        'image' => 'https://example.test/locker.png',
                        'is_cod_available' => true,
                        'is_for_sender' => false,
                        'is_for_receiver' => true,
                        'locality_id' => 418,
                        'open_hours' => [
                            'monday' => ['opened' => '00:00', 'closed' => '23:59'],
                            'tuesday' => ['opened' => '00:00', 'closed' => '23:59'],
                            'wednesday' => ['opened' => '00:00', 'closed' => '23:59'],
                            'thursday' => ['opened' => '00:00', 'closed' => '23:59'],
                            'friday' => ['opened' => '00:00', 'closed' => '23:59'],
                            'saturday' => ['opened' => '00:00', 'closed' => '23:59'],
                            'sunday' => ['opened' => '00:00', 'closed' => '23:59'],
                        ],
                        'locality' => [
                            'id' => 418,
                            'name' => 'Bacau',
                            'municipality' => 'Bacau',
                            'postal_code' => '600000',
                            'has_streets' => true,
                            'county' => [
                                'id' => 4,
                                'name' => 'Bacau',
                                'code' => 'BC',
                            ],
                        ],
                    ]],
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

        $this->assertCount(1, $result->mapPoints->mapPoints);
        $this->assertSame('Point A', $result->mapPoints->mapPoints[0]->name);
        $this->assertSame('BC', $result->mapPoints->mapPoints[0]->locality->county->code);
        $this->assertSame('00:00', $result->mapPoints->mapPoints[0]->openHours->monday->opened);
        $this->assertSame('/api/v1/map-points/RO', $httpClient->lastRequest?->getUri()->getPath());
        $this->assertSame('application/json', $httpClient->lastRequest?->getHeaderLine('Content-Type'));
    }
}
