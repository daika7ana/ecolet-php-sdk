<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\Resources;

use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Config\ClientConfig;
use Daika7ana\Ecolet\Tests\Support\FakeHttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class LocationsResourceTest extends TestCase
{
    public function testGetCountriesReturnsCollection(): void
    {
        $httpClient = new FakeHttpClient(
            static fn() => new Response(200, [], json_encode([
                ['code' => 'RO', 'name' => 'Romania'],
                ['code' => 'BG', 'name' => 'Bulgaria'],
            ], JSON_THROW_ON_ERROR)),
        );
        $factory = new HttpFactory();

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: ClientConfig::fromEnvironment(),
        );

        $countries = $client->locations()->getCountries();

        $this->assertSame(2, $countries->count());
        $this->assertSame('/api/v1/locations/countries', $httpClient->lastRequest?->getUri()->getPath());
    }
}
