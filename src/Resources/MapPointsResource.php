<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Resources;

use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\DTOs\MapPoints\MapPoint;
use Daika7ana\Ecolet\DTOs\MapPoints\MapPointsResponse;
use Daika7ana\Ecolet\Exceptions\UnexpectedStatusException;
use Daika7ana\Ecolet\Exceptions\ValidationException;
use Daika7ana\Ecolet\Support\ApiResponseMapper;
use Daika7ana\Ecolet\Support\JsonHelper;
use Daika7ana\Ecolet\Support\JsonStream;
use Generator;
use JsonException;
use Psr\Http\Message\ResponseInterface;

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
    public function getMapPoints(string $countryCode, array $filters = []): MapPointsResponse
    {
        return ApiResponseMapper::mapJson(
            $this->getMapPointsRaw($countryCode, $filters),
            static fn(array $data) => MapPointsResponse::fromArray(ApiResponseMapper::arrayOrEmpty($data['mapPoints'] ?? null)),
        );
    }

    /**
     * Stream map points for a specific country.
     *
     * Yields one MapPoint at a time and keeps only the current record in memory,
     * so very large responses are never decoded all at once. The bounding box is
     * not available in this mode; use getMapPoints() when you need it. Yields
     * nothing when the response contains no map points.
     *
     * @param string $countryCode ISO country code
     * @param array<mixed> $filters Optional filters for the map points
     *
     * @return Generator<int, MapPoint>
     *
     * @throws UnexpectedStatusException
     * @throws ValidationException
     * @throws JsonException
     */
    public function streamMapPoints(string $countryCode, array $filters = []): Generator
    {
        $stream = $this->getMapPointsRaw($countryCode, $filters)->getBody();

        foreach ((new JsonStream($stream))->arrayAt(['mapPoints', 'mapPoints']) as $mapPoint) {
            /** @var array<string, mixed> $mapPoint */
            yield MapPoint::fromArray($mapPoint);
        }
    }

    /**
     * Get map points for a specific country without decoding the body.
     *
     * The response can be very large; this keeps it as a PSR-7 stream so it can
     * be fed to a streaming JSON parser instead of being decoded into DTOs at once.
     *
     * @param string $countryCode ISO country code
     * @param array<mixed> $filters Optional filters for the map points
     *
     * @throws UnexpectedStatusException
     * @throws ValidationException
     */
    public function getMapPointsRaw(string $countryCode, array $filters = []): ResponseInterface
    {
        $request = $this->client->createRequest('POST', sprintf('/v1/map-points/%s', rawurlencode(strtolower($countryCode))));

        $body = JsonHelper::encode(array_merge(['destination' => 'receiver'], $filters));
        $request = $request->withBody($this->client->streamFactory()->createStream($body))
            ->withHeader('Content-Type', 'application/json');

        $response = $this->client->send($request);
        ApiResponseMapper::assertStatus($response, 200);

        return $response;
    }
}
