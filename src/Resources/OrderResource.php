<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Resources;

use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\DTOs\Common\Collection;
use Daika7ana\Ecolet\DTOs\Orders\DeleteOrderResult;
use Daika7ana\Ecolet\DTOs\Orders\Order;
use Daika7ana\Ecolet\DTOs\Orders\OrderWithStatuses;
use Daika7ana\Ecolet\DTOs\Orders\WaybillDocument;
use Daika7ana\Ecolet\Exceptions\UnexpectedStatusException;
use Daika7ana\Ecolet\Exceptions\ValidationException;
use Daika7ana\Ecolet\Support\ApiResponseMapper;
use Daika7ana\Ecolet\Support\JsonHelper;

class OrderResource
{
    public function __construct(
        private Client $client,
    ) {}

    /**
     * Get an order by ID.
     *
     * @throws UnexpectedStatusException
     * @throws ValidationException
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
     * @throws UnexpectedStatusException
     * @throws ValidationException
     */
    public function deleteOrder(int $id): DeleteOrderResult
    {
        $request = $this->client->createRequest('DELETE', sprintf('/v1/order/%d', $id));
        $response = $this->client->send($request);

        $data = ApiResponseMapper::decodeJson($response);

        return DeleteOrderResult::fromArray($data);
    }

    /**
     * Download waybill for an order as a stream.
     *
     * @throws UnexpectedStatusException
     * @throws ValidationException
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
     * @param list<string> $awbs
     *
     * @return Collection<int, OrderWithStatuses>
     *
     * @throws UnexpectedStatusException
     * @throws ValidationException
     */
    public function getStatusesForManyOrders(array $awbs): Collection
    {
        $request = $this->client->createRequest('POST', '/v1/order/get-statuses-for-many-orders');

        $body = JsonHelper::encode([
            'awbs' => array_map(static fn(mixed $awb): string => (string) $awb, $awbs),
        ]);
        $request = $request->withBody($this->client->streamFactory()->createStream($body))
            ->withHeader('Content-Type', 'application/json');

        $response = $this->client->send($request);

        $data = ApiResponseMapper::decodeJson($response);

        /** @var list<array<string, mixed>> $statusItems */
        $statusItems = $data['data'] ?? [];

        $statuses = array_map(
            static fn(array $item) => OrderWithStatuses::fromArray($item),
            $statusItems,
        );

        return new Collection(array_values($statuses));
    }
}
