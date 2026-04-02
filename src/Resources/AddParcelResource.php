<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Resources;

use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\DTOs\AddParcelRequest;
use Daika7ana\Ecolet\DTOs\AddParcelResult;
use Daika7ana\Ecolet\Support\ApiResponseMapper;
use Daika7ana\Ecolet\Support\JsonHelper;

/**
 * Add Parcel resource — all operations use /api/v2 endpoints.
 */
class AddParcelResource
{
    public function __construct(
        private Client $client,
    ) {}

    /**
     * Reload the form (v2 endpoint).
     *
    * @param AddParcelRequest|array<string, mixed> $data Form data
     *
    * @throws \Daika7ana\Ecolet\Exceptions\UnexpectedStatusException
    * @throws \Daika7ana\Ecolet\Exceptions\ValidationException
     */
    public function reloadForm(AddParcelRequest|array $data): AddParcelResult
    {
        $request = $this->client->createRequest('POST', '/v2/add-parcel/reload-form');

        $body = JsonHelper::encode($this->normalizeRequestData($data));
        $request = $request->withBody($this->client->streamFactory()->createStream($body))
            ->withHeader('Content-Type', 'application/json');

        $response = $this->client->send($request);

        return ApiResponseMapper::mapJson(
            $response,
            static fn(array $data) => AddParcelResult::fromArray($data),
        );
    }

    /**
     * Send an order (v2 endpoint).
     *
    * @param AddParcelRequest|array<string, mixed> $data Order data (supports multiple parcels)
     *
    * @throws \Daika7ana\Ecolet\Exceptions\UnexpectedStatusException
    * @throws \Daika7ana\Ecolet\Exceptions\ValidationException
     */
    public function sendOrder(AddParcelRequest|array $data): AddParcelResult
    {
        $request = $this->client->createRequest('POST', '/v2/add-parcel/send-order');

        $body = JsonHelper::encode($this->normalizeRequestData($data));
        $request = $request->withBody($this->client->streamFactory()->createStream($body))
            ->withHeader('Content-Type', 'application/json');

        $response = $this->client->send($request);

        return ApiResponseMapper::mapJson(
            $response,
            static fn(array $data) => AddParcelResult::fromArray($data),
        );
    }

    /**
     * Save order to send (v2 endpoint).
     *
    * @param AddParcelRequest|array<string, mixed> $data Order data (supports multiple parcels)
     *
    * @throws \Daika7ana\Ecolet\Exceptions\UnexpectedStatusException
    * @throws \Daika7ana\Ecolet\Exceptions\ValidationException
     */
    public function saveOrderToSend(AddParcelRequest|array $data): AddParcelResult
    {
        $request = $this->client->createRequest('POST', '/v2/add-parcel/save-order-to-send');

        $body = JsonHelper::encode($this->normalizeRequestData($data));
        $request = $request->withBody($this->client->streamFactory()->createStream($body))
            ->withHeader('Content-Type', 'application/json');

        $response = $this->client->send($request);

        return ApiResponseMapper::mapJson(
            $response,
            static fn(array $data) => AddParcelResult::fromArray($data),
        );
    }

    /**
     * @param AddParcelRequest|array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function normalizeRequestData(AddParcelRequest|array $data): array
    {
        if ($data instanceof AddParcelRequest) {
            return $data->toArray();
        }

        return $data;
    }
}
