<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\Resources;

use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Config\ClientConfig;
use Daika7ana\Ecolet\DTOs\Locations\StreetPostalCode;
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
                'data' => [
                    ['code' => 'RO', 'name' => 'Romania'],
                    ['code' => 'BG', 'name' => 'Bulgaria'],
                ],
            ], JSON_THROW_ON_ERROR)),
        );
        $factory = new HttpFactory();

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: new ClientConfig(),
        );

        $countries = $client->locations()->getCountries();

        $this->assertSame(2, $countries->count());
        $this->assertSame('/api/v1/locations/countries', $httpClient->lastRequest?->getUri()->getPath());
    }

    public function testSearchStreetPostalCodesReturnsTypedCollection(): void
    {
        $httpClient = new FakeHttpClient(
            static fn() => new Response(200, [], json_encode([
                'postal_codes' => [
                    ['code' => '010371', 'number' => '1-7', 'block' => null],
                    ['code' => '010372', 'number' => null, 'block' => 'A'],
                ],
            ], JSON_THROW_ON_ERROR)),
        );
        $factory = new HttpFactory();

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: new ClientConfig(),
        );

        $postalCodes = $client->locations()->searchStreetPostalCodes(13751, 'Piaţă Romană');
        $firstPostalCode = $postalCodes->first();

        $this->assertSame(2, $postalCodes->count());
        $this->assertInstanceOf(StreetPostalCode::class, $firstPostalCode);
        $this->assertSame('010371', $firstPostalCode->code);
        $this->assertSame('1-7', $firstPostalCode->number);
        $this->assertNull($firstPostalCode->block);
        $this->assertSame('/api/v1/locations/13751/search-street-postal-codes/Pia%C5%A3%C4%83+Roman%C4%83', $httpClient->lastRequest?->getUri()->getPath());
    }
}
