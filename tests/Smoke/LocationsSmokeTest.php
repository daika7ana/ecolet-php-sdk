<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Smoke;

use Daika7ana\Ecolet\DTOs\Common\Collection;
use Daika7ana\Ecolet\DTOs\Locations\Country;
use Daika7ana\Ecolet\DTOs\Locations\County;
use Daika7ana\Ecolet\DTOs\Locations\Locality;
use Daika7ana\Ecolet\DTOs\Locations\Street;
use Daika7ana\Ecolet\DTOs\Locations\StreetPostalCode;
use Daika7ana\Ecolet\Tests\Smoke\Concerns\InteractsWithAuthenticatedSmokeClient;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

final class LocationsSmokeTest extends TestCase
{
    use InteractsWithAuthenticatedSmokeClient;

    #[Group('smoke')]
    public function testGetCountriesReturnsNonEmptyCollection(): void
    {
        $client = $this->makeAuthenticatedClient('locations');

        $countries = $client->locations()->getCountries();

        $this->assertInstanceOf(Collection::class, $countries);
        $this->assertNotEmpty($countries->items);
        $this->assertInstanceOf(Country::class, $countries->items[0]);
        $this->assertNotSame('', $countries->items[0]->code);
        $this->assertNotSame('', $countries->items[0]->name);
    }

    #[Group('smoke')]
    public function testGetCountiesForRomania(): void
    {
        $client = $this->makeAuthenticatedClient('locations');

        $counties = $client->locations()->getCounties('RO');

        $this->assertInstanceOf(Collection::class, $counties);
        $this->assertNotEmpty($counties->items);
        $this->assertInstanceOf(County::class, $counties->items[0]);
        $this->assertGreaterThan(0, $counties->items[0]->id);
        $this->assertNotSame('', $counties->items[0]->name);
    }

    #[Group('smoke')]
    public function testSearchLocalitiesReturnsMatchingResults(): void
    {
        $client = $this->makeAuthenticatedClient('locations');

        $localities = $client->locations()->searchLocalities('RO', 'Cluj');

        $this->assertInstanceOf(Collection::class, $localities);
        $this->assertNotEmpty($localities->items);
        $this->assertInstanceOf(Locality::class, $localities->items[0]);
        $this->assertGreaterThan(0, $localities->items[0]->id);
        $this->assertNotSame('', $localities->items[0]->name);
    }

    #[Group('smoke')]
    public function testSearchStreetsReturnsMatchingResults(): void
    {
        $client = $this->makeAuthenticatedClient('locations');

        $localities = $client->locations()->searchLocalities('RO', 'Cluj-Napoca');
        $this->assertNotEmpty($localities->items);

        $localityId = $localities->items[0]->id;
        $streets = $client->locations()->searchStreets($localityId, 'Mihai');

        $this->assertInstanceOf(Collection::class, $streets);
        $this->assertNotEmpty($streets->items);
        $this->assertIsString($streets->items[0]);
    }

    #[Group('smoke')]
    public function testSearchStreetPostalCodesReturnsResults(): void
    {
        $client = $this->makeAuthenticatedClient('locations');

        $localities = $client->locations()->searchLocalities('RO', 'Cluj-Napoca');
        $this->assertNotEmpty($localities->items);

        $localityId = $localities->items[0]->id;
        $postalCodes = $client->locations()->searchStreetPostalCodes($localityId, 'Ierbii');

        $this->assertInstanceOf(Collection::class, $postalCodes);
        $this->assertNotEmpty($postalCodes->items);

        foreach ($postalCodes->items as $code) {
            $this->assertInstanceOf(StreetPostalCode::class, $code);
            $this->assertNotSame('', $code->code);
        }
    }

    #[Group('smoke')]
    public function testSearchStreetsByPostalCodeReturnsResults(): void
    {
        $client = $this->makeAuthenticatedClient('locations');

        $streets = $client->locations()->searchStreetsByPostalCode('RO', '400001');

        $this->assertInstanceOf(Collection::class, $streets);
        $this->assertNotEmpty($streets->items);
        $this->assertInstanceOf(Street::class, $streets->items[0]);
    }

}
