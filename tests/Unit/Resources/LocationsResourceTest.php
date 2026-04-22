<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\Resources;

use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Config\ClientConfig;
use Daika7ana\Ecolet\DTOs\Locations\Country;
use Daika7ana\Ecolet\DTOs\Locations\County;
use Daika7ana\Ecolet\DTOs\Locations\Locality;
use Daika7ana\Ecolet\DTOs\Locations\Street;
use Daika7ana\Ecolet\DTOs\Locations\StreetPostalCode;
use Daika7ana\Ecolet\DTOs\Locations\StreetsByPostalCodeResult;
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
                    ['id' => 1, 'code' => 'RO', 'name' => 'Romania', 'is_default' => true, 'has_counties' => true],
                    ['id' => 2, 'code' => 'BG', 'name' => 'Bulgaria', 'is_default' => false, 'has_counties' => true],
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
        $firstCountry = $countries->first();

        $this->assertSame(2, $countries->count());
        $this->assertInstanceOf(Country::class, $firstCountry);
        $this->assertSame(1, $firstCountry->id);
        $this->assertSame('RO', $firstCountry->code);
        $this->assertSame('Romania', $firstCountry->name);
        $this->assertTrue($firstCountry->isDefault);
        $this->assertTrue($firstCountry->hasCounties);
        $this->assertSame('/api/v1/locations/countries', $httpClient->lastRequest?->getUri()->getPath());
    }

    public function testGetCountiesMapsCountyCode(): void
    {
        $httpClient = new FakeHttpClient(
            static fn() => new Response(200, [], json_encode([
                'data' => [
                    ['id' => 10, 'name' => 'Bucuresti', 'code' => 'B'],
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

        $counties = $client->locations()->getCounties('RO');
        $firstCounty = $counties->first();

        $this->assertSame(1, $counties->count());
        $this->assertInstanceOf(County::class, $firstCounty);
        $this->assertSame(10, $firstCounty->id);
        $this->assertSame('Bucuresti', $firstCounty->name);
        $this->assertSame('B', $firstCounty->code);
        $this->assertSame('/api/v1/locations/RO/counties', $httpClient->lastRequest?->getUri()->getPath());
    }

    public function testSearchLocalitiesMapsSchemaFields(): void
    {
        $httpClient = new FakeHttpClient(
            static fn() => new Response(200, [], json_encode([
                'localities' => [
                    [
                        'id' => 13751,
                        'name' => 'Bucuresti',
                        'municipality' => 'Sectorul 1',
                        'postal_code' => '011318',
                        'has_streets' => true,
                        'county' => [
                            'id' => 10,
                            'name' => 'Bucuresti',
                            'code' => 'B',
                        ],
                    ],
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

        $localities = $client->locations()->searchLocalities('RO', 'Bucu');
        $firstLocality = $localities->first();

        $this->assertSame(1, $localities->count());
        $this->assertInstanceOf(Locality::class, $firstLocality);
        $this->assertSame(13751, $firstLocality->id);
        $this->assertSame('Bucuresti', $firstLocality->name);
        $this->assertSame('Sectorul 1', $firstLocality->municipality);
        $this->assertSame('011318', $firstLocality->postalCode);
        $this->assertTrue($firstLocality->hasStreets);
        $this->assertInstanceOf(County::class, $firstLocality->county);
        $this->assertSame('B', $firstLocality->county->code);
        $this->assertSame('/api/v1/locations/RO/localities/Bucu', $httpClient->lastRequest?->getUri()->getPath());
    }

    public function testSearchStreetsUsesRawUrlEncodingForQuery(): void
    {
        $httpClient = new FakeHttpClient(
            static fn() => new Response(200, [], json_encode([
                'streets' => ['Piaţă Romană'],
            ], JSON_THROW_ON_ERROR)),
        );
        $factory = new HttpFactory();

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: new ClientConfig(),
        );

        $streets = $client->locations()->searchStreets(13751, 'Piaţă Romană');

        $this->assertSame(1, $streets->count());
        $this->assertSame('Piaţă Romană', $streets->first());
        $this->assertSame('/api/v1/locations/13751/streets/Pia%C5%A3%C4%83%20Roman%C4%83', $httpClient->lastRequest?->getUri()->getPath());
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
        $this->assertSame('/api/v1/locations/13751/search-street-postal-codes/Pia%C5%A3%C4%83%20Roman%C4%83', $httpClient->lastRequest?->getUri()->getPath());
    }

    public function testSearchStreetsByPostalCodeKeepsValidationFlagAndStreetDetails(): void
    {
        $httpClient = new FakeHttpClient(
            static fn() => new Response(200, [], json_encode([
                'is_valid' => true,
                'streets' => [
                    [
                        'id' => 959,
                        'name' => 'Piaţă Romană',
                        'postal_code' => '010371',
                        'locality' => [
                            'id' => 13751,
                            'name' => 'Bucuresti',
                            'municipality' => 'Sectorul 1',
                            'postal_code' => '011318',
                            'has_streets' => true,
                            'county' => [
                                'id' => 10,
                                'name' => 'Bucuresti',
                                'code' => 'B',
                            ],
                        ],
                    ],
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

        $result = $client->locations()->searchStreetsByPostalCode('RO', '010371');
        $street = $result->streets->first();

        $this->assertInstanceOf(StreetsByPostalCodeResult::class, $result);
        $this->assertTrue($result->isValid);
        $this->assertSame(1, $result->streets->count());
        $this->assertInstanceOf(Street::class, $street);
        $this->assertSame(959, $street->id);
        $this->assertSame('Piaţă Romană', $street->name);
        $this->assertSame('010371', $street->postalCode);
        $this->assertInstanceOf(Locality::class, $street->locality);
        $this->assertSame('Bucuresti', $street->locality->name);
        $this->assertInstanceOf(County::class, $street->locality->county);
        $this->assertSame('B', $street->locality->county->code);
        $this->assertSame('/api/v1/locations/RO/search-streets-by-postal-code/010371', $httpClient->lastRequest?->getUri()->getPath());
    }
}
