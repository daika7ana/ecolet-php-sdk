<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Resources;

use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\DTOs\AddressBook\AddressBookResult;
use Daika7ana\Ecolet\Exceptions\UnexpectedStatusException;
use Daika7ana\Ecolet\Exceptions\ValidationException;
use Daika7ana\Ecolet\Support\ApiResponseMapper;

class AddressBookResource
{
    public function __construct(
        private Client $client,
    ) {}

    /**
     * Get the authenticated user's address book.
     *
     * @param int|null $page Page number (1-based)
     * @param string|null $searchQuery Search term, minimum 3 characters
     *
     * @throws UnexpectedStatusException
     * @throws ValidationException
     */
    public function getAddresses(?int $page = null, ?string $searchQuery = null): AddressBookResult
    {
        $query = array_filter([
            'page' => $page,
            'searchQuery' => $searchQuery,
        ], static fn(mixed $value): bool => $value !== null);

        $path = '/v1/address-book';

        if ($query !== []) {
            $path .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }

        $request = $this->client->createRequest('GET', $path);
        $response = $this->client->send($request);

        return AddressBookResult::fromArray(ApiResponseMapper::decodeJson($response));
    }
}
