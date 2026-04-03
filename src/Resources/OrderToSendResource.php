<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Resources;

use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\DTOs\Orders\OrderToSend;
use Daika7ana\Ecolet\Support\ApiResponseMapper;

class OrderToSendResource
{
    public function __construct(
        private Client $client,
    ) {}

    /**
     * Get an order to send by ID.
     *
     * @throws \Daika7ana\Ecolet\Exceptions\UnexpectedStatusException
     * @throws \Daika7ana\Ecolet\Exceptions\ValidationException
     */
    public function getOrderToSend(int $id): OrderToSend
    {
        $request = $this->client->createRequest('GET', sprintf('/v1/order-to-send/%d', $id));
        $response = $this->client->send($request);

        $data = ApiResponseMapper::decodeJson($response);

        return OrderToSend::fromArray($data);
    }
}
