<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Resources;

use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\DTOs\Common\Collection;
use Daika7ana\Ecolet\DTOs\Locations\Country;
use Daika7ana\Ecolet\DTOs\Locations\County;
use Daika7ana\Ecolet\DTOs\Locations\Locality;
use Daika7ana\Ecolet\DTOs\Locations\Street;
use Daika7ana\Ecolet\DTOs\Locations\StreetPostalCode;
use Daika7ana\Ecolet\Exceptions\UnexpectedStatusException;
use Daika7ana\Ecolet\Exceptions\ValidationException;
use Daika7ana\Ecolet\Support\ApiResponseMapper;

class LocationsResource
{
    public function __construct(
        private Client $client,
    ) {}

    /**
     * Get all available countries.
     *
     * @return Collection<int, Country>
     *
    * @throws UnexpectedStatusException
    * @throws ValidationException
     */
    public function getCountries(): Collection
    {
        $request = $this->client->createRequest('GET', '/v1/locations/countries');
        $response = $this->client->send($request);
        $data = ApiResponseMapper::decodeJson($response);

        $countries = array_map(
            static fn(array $item) => Country::fromArray($item),
            $data['data'],
        );

        return new Collection($countries);
    }

    /**
     * Get counties for a specific country.
     *
     * @return Collection<int, County>
     *
    * @throws UnexpectedStatusException
    * @throws ValidationException
     */
    public function getCounties(string $countryCode): Collection
    {
        $request = $this->client->createRequest(
            'GET',
            sprintf('/v1/locations/%s/counties', $countryCode),
        );
        $response = $this->client->send($request);

        $data = ApiResponseMapper::decodeJson($response);

        $counties = array_map(
            static fn(array $item) => County::fromArray($item),
            $data['data'],
        );

        return new Collection($counties);
    }

    /**
     * Search localities by country code and search query.
     *
     * @return Collection<int, Locality>
     *
    * @throws UnexpectedStatusException
    * @throws ValidationException
     */
    public function searchLocalities(string $countryCode, string $searchQuery): Collection
    {
        $request = $this->client->createRequest(
            'GET',
            sprintf('/v1/locations/%s/localities/%s', $countryCode, urlencode($searchQuery)),
        );
        $response = $this->client->send($request);

        $data = ApiResponseMapper::decodeJson($response);

        $localities = array_map(
            static fn(array $item) => Locality::fromArray($item),
            $data['localities'],
        );

        return new Collection($localities);
    }

    /**
     * Search streets by locality.
     *
     * @return Collection<int, string>
     *
    * @throws UnexpectedStatusException
    * @throws ValidationException
     */
    public function searchStreets(int $localityId, string $searchQuery): Collection
    {
        $request = $this->client->createRequest(
            'GET',
            sprintf('/v1/locations/%d/streets/%s', $localityId, urlencode($searchQuery)),
        );
        $response = $this->client->send($request);

        $data = ApiResponseMapper::decodeJson($response);

        return new Collection($data['streets']);
    }

    /**
     * Get postal codes for a street.
     *
     * @return Collection<int, StreetPostalCode>
     *
    * @throws UnexpectedStatusException
    * @throws ValidationException
     */
    public function searchStreetPostalCodes(int $localityId, string $streetName): Collection
    {
        $request = $this->client->createRequest(
            'GET',
            sprintf('/v1/locations/%d/search-street-postal-codes/%s', $localityId, urlencode($streetName)),
        );
        $response = $this->client->send($request);

        $data = ApiResponseMapper::decodeJson($response);

        /** @var list<array{code: string, number?: string|null, block?: string|null}> $postalCodeData */
        $postalCodeData = $data['postal_codes'];

        $postalCodes = array_map(
            static fn(array $item): StreetPostalCode => StreetPostalCode::fromArray($item),
            $postalCodeData,
        );

        return new Collection($postalCodes);
    }

    /**
     * Search streets by postal code and country.
     *
     * @return Collection<int, Street>
     *
    * @throws UnexpectedStatusException
    * @throws ValidationException
     */
    public function searchStreetsByPostalCode(string $countryCode, string $postalCode): Collection
    {
        $request = $this->client->createRequest(
            'GET',
            sprintf('/v1/locations/%s/search-streets-by-postal-code/%s', $countryCode, urlencode($postalCode)),
        );
        $response = $this->client->send($request);

        $data = ApiResponseMapper::decodeJson($response);

        $streets = array_map(
            static fn(array $item) => Street::fromArray($item),
            $data['streets'],
        );

        return new Collection($streets);
    }
}
