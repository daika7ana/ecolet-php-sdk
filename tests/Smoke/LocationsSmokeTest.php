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
        $firstCountry = $countries->first();

        $this->assertInstanceOf(Collection::class, $countries);
        $this->assertGreaterThan(0, $countries->count());
        $this->assertInstanceOf(Country::class, $firstCountry);
        $this->assertNotSame('', $firstCountry->code);
        $this->assertNotSame('', $firstCountry->name);
    }

    #[Group('smoke')]
    public function testGetCountiesForRomania(): void
    {
        $client = $this->makeAuthenticatedClient('locations');

        $counties = $client->locations()->getCounties('RO');
        $firstCounty = $counties->first();

        $this->assertInstanceOf(Collection::class, $counties);
        $this->assertGreaterThan(0, $counties->count());
        $this->assertInstanceOf(County::class, $firstCounty);
        $this->assertGreaterThan(0, $firstCounty->id);
        $this->assertNotSame('', $firstCounty->name);
    }

    #[Group('smoke')]
    public function testSearchLocalitiesReturnsMatchingResults(): void
    {
        $client = $this->makeAuthenticatedClient('locations');

        $localities = $client->locations()->searchLocalities('RO', 'Cluj');
        $firstLocality = $localities->first();

        $this->assertInstanceOf(Collection::class, $localities);
        $this->assertGreaterThan(0, $localities->count());
        $this->assertInstanceOf(Locality::class, $firstLocality);
        $this->assertGreaterThan(0, $firstLocality->id);
        $this->assertNotSame('', $firstLocality->name);
    }

    #[Group('smoke')]
    public function testSearchStreetsReturnsMatchingResults(): void
    {
        $client = $this->makeAuthenticatedClient('locations');

        $localities = $client->locations()->searchLocalities('RO', 'Cluj-Napoca');
        $firstLocality = $localities->first();

        $this->assertGreaterThan(0, $localities->count());
        $this->assertInstanceOf(Locality::class, $firstLocality);

        $localityId = $firstLocality->id;
        $streets = $client->locations()->searchStreets($localityId, 'Mihai');
        $firstStreet = $streets->first();

        $this->assertInstanceOf(Collection::class, $streets);
        $this->assertGreaterThan(0, $streets->count());
        $this->assertIsString($firstStreet);
    }

    #[Group('smoke')]
    public function testSearchStreetPostalCodesReturnsResults(): void
    {
        $client = $this->makeAuthenticatedClient('locations');

        $localities = $client->locations()->searchLocalities('RO', 'Cluj-Napoca');
        $firstLocality = $localities->first();

        $this->assertGreaterThan(0, $localities->count());
        $this->assertInstanceOf(Locality::class, $firstLocality);

        $localityId = $firstLocality->id;
        $postalCodes = $client->locations()->searchStreetPostalCodes($localityId, 'Ierbii');

        $this->assertInstanceOf(Collection::class, $postalCodes);
        $this->assertGreaterThan(0, $postalCodes->count());

        foreach ($postalCodes as $code) {
            $this->assertInstanceOf(StreetPostalCode::class, $code);
            $this->assertNotSame('', $code->code);
        }
    }

    #[Group('smoke')]
    public function testSearchStreetsByPostalCodeReturnsResults(): void
    {
        $client = $this->makeAuthenticatedClient('locations');

        $streets = $client->locations()->searchStreetsByPostalCode('RO', '400001');
        $firstStreet = $streets->first();

        $this->assertInstanceOf(Collection::class, $streets);
        $this->assertGreaterThan(0, $streets->count());
        $this->assertInstanceOf(Street::class, $firstStreet);
    }

}
