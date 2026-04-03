<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Resources;

use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\DTOs\Common\Collection;
use Daika7ana\Ecolet\DTOs\Orders\Order;
use Daika7ana\Ecolet\DTOs\Orders\OrderStatus;
use Daika7ana\Ecolet\DTOs\Orders\WaybillDocument;
use Daika7ana\Ecolet\Support\JsonHelper;
use Daika7ana\Ecolet\Support\ApiResponseMapper;

class OrderResource
{
    public function __construct(
        private Client $client,
    ) {}

    /**
     * Get an order by ID.
     *
    * @throws \Daika7ana\Ecolet\Exceptions\UnexpectedStatusException
    * @throws \Daika7ana\Ecolet\Exceptions\ValidationException
     */
    public function getOrder(int $id): Order
    {
        $request = $this->client->createRequest('GET', sprintf('/v1/order/%d', $id));
        $response = $this->client->send($request);

        $data = ApiResponseMapper::decodeJson($response);

        return Order::fromArray($data);
    }

    /**
     * Delete an order by ID.
     *
    * @throws \Daika7ana\Ecolet\Exceptions\UnexpectedStatusException
    * @throws \Daika7ana\Ecolet\Exceptions\ValidationException
     */
    public function deleteOrder(int $id): void
    {
        $request = $this->client->createRequest('DELETE', sprintf('/v1/order/%d', $id));
        $response = $this->client->send($request);

        ApiResponseMapper::assertStatus($response, 204);
    }

    /**
     * Download waybill for an order as a stream.
     *
    * @throws \Daika7ana\Ecolet\Exceptions\UnexpectedStatusException
    * @throws \Daika7ana\Ecolet\Exceptions\ValidationException
     */
    public function downloadWaybill(int $id): WaybillDocument
    {
        $request = $this->client->createRequest('GET', sprintf('/v1/order/%d/download-waybill', $id));
        $response = $this->client->send($request);

        ApiResponseMapper::assertStatus($response, 200);

        return new WaybillDocument(
            stream: $response->getBody(),
            contentType: $response->getHeaderLine('Content-Type') ?: null,
            contentDisposition: $response->getHeaderLine('Content-Disposition') ?: null,
        );
    }

    /**
     * Get statuses for multiple orders.
     *
     * @param list<int> $orderIds
     *
     * @return Collection<OrderStatus>
     *
    * @throws \Daika7ana\Ecolet\Exceptions\UnexpectedStatusException
    * @throws \Daika7ana\Ecolet\Exceptions\ValidationException
     */
    public function getStatusesForManyOrders(array $orderIds): Collection
    {
        $request = $this->client->createRequest('POST', '/v1/order/get-statuses-for-many-orders');

        $body = JsonHelper::encode(['order_ids' => $orderIds]);
        $request = $request->withBody($this->client->streamFactory()->createStream($body))
            ->withHeader('Content-Type', 'application/json');

        $response = $this->client->send($request);

        $data = ApiResponseMapper::decodeJson($response);

        $statuses = array_map(
            static fn(array $item) => OrderStatus::fromArray($item),
            $data,
        );

        return new Collection($statuses);
    }
}
