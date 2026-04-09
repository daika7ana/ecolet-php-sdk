<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Resources;

use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\DTOs\MapPoints\MapPointsResult;
use Daika7ana\Ecolet\Exceptions\UnexpectedStatusException;
use Daika7ana\Ecolet\Exceptions\ValidationException;
use Daika7ana\Ecolet\Support\ApiResponseMapper;
use Daika7ana\Ecolet\Support\JsonHelper;

class MapPointsResource
{
    public function __construct(
        private Client $client,
    ) {}

    /**
     * Get map points for a specific country.
     *
     * @param string $countryCode ISO country code
     * @param array<mixed> $filters Optional filters for the map points
     *
        * @throws UnexpectedStatusException
        * @throws ValidationException
     */
    public function getMapPoints(string $countryCode, array $filters = []): MapPointsResult
    {
        $request = $this->client->createRequest('POST', sprintf('/v1/map-points/%s', $countryCode));

        $body = JsonHelper::encode(array_merge(['destination' => 'receiver'], $filters));
        $request = $request->withBody($this->client->streamFactory()->createStream($body))
            ->withHeader('Content-Type', 'application/json');

        $response = $this->client->send($request);

        return ApiResponseMapper::mapJson(
            $response,
            static fn(array $data) => MapPointsResult::fromArray($data),
        );
    }
}
