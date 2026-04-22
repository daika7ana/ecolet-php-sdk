<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\Resources;

use Daika7ana\Ecolet\Exceptions\UnexpectedStatusException;
use Daika7ana\Ecolet\Exceptions\ValidationException;
use Daika7ana\Ecolet\Tests\Support\ResponseFixtureFactory;
use Daika7ana\Ecolet\Tests\Support\TestClientFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class OrderResourceTest extends TestCase
{
    public function testGetOrderReturnsDto(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => new Response(200, [], json_encode([
                'data' => [
                    'id' => 123,
                    'service' => [
                        'slug' => 'dpd_standard',
                        'full_name' => 'DPD Standard',
                        'courier_slug' => 'dpd',
                        'courier_name' => 'DPD',
                    ],
                    'shipment_type' => 'primary',
                    'primary_order_awb' => null,
                    'sender' => [
                        'id' => 1,
                        'name' => 'Sender Company',
                        'locality_id' => 13751,
                        'country' => 'ro',
                        'county' => 'Bucuresti',
                        'locality' => 'Bucuresti',
                        'postal_code' => '011318',
                        'has_streets' => true,
                        'street_name' => 'Bucuresti-Ploiesti',
                        'street_number' => '10',
                        'contact_person' => 'John Doe',
                        'email' => 'sender@example.com',
                        'phone' => '0711111111',
                        'map_point_id' => null,
                        'map_point_name' => null,
                        'created_at' => '2022-12-20T10:00:00.000000Z',
                    ],
                    'receiver' => [
                        'id' => 2,
                        'name' => 'Receiver Company',
                        'locality_id' => 13751,
                        'country' => 'ro',
                        'county' => 'Bucuresti',
                        'locality' => 'Bucuresti',
                        'postal_code' => '011318',
                        'has_streets' => true,
                        'street_name' => 'Unirii',
                        'street_number' => '15',
                        'contact_person' => 'Jane Doe',
                        'email' => 'receiver@example.com',
                        'phone' => '0722222222',
                        'map_point_id' => 12,
                        'map_point_name' => 'Locker 12',
                        'created_at' => '2022-12-20T10:05:00.000000Z',
                    ],
                    'awb' => '80438360579',
                    'waybill_extension' => 'pdf',
                    'waybill_has_been_downloaded' => true,
                    'status' => 'new',
                    'type' => 'package',
                    'amount' => 1,
                    'price' => 16.28,
                    'content' => 'books',
                    'shape' => 'standard',
                    'weight' => 1,
                    'length' => 10,
                    'width' => 15,
                    'height' => 10,
                    'declared_value' => 120.5,
                    'cod' => 500.0,
                    'cod_received_at' => '2022-12-22T10:00:00.000000Z',
                    'cod_returned_at' => null,
                    'observations' => 'Leave at reception.',
                    'pickup_date' => '2022-12-21',
                    'pickup_hour' => '13:00',
                    'fees' => [
                        ['type' => 'base', 'value' => '15.50'],
                        ['type' => 'fuel_surcharge', 'value' => '0.78'],
                    ],
                    'vat' => 19,
                    'statuses' => [
                        [
                            'name' => 'new',
                            'real_name' => 'Shipment data received',
                            'created_at' => '2022-12-21T19:43:40.000000Z',
                        ],
                    ],
                    'updated_at' => '2022-12-21T19:43:40.000000Z',
                    'created_at' => '2022-12-21T19:40:00.000000Z',
                ],
            ], JSON_THROW_ON_ERROR)),
        );

        $order = $client->orders()->getOrder(123);

        $this->assertSame('80438360579', $order->number);
        $this->assertSame('80438360579', $order->awb);
        $this->assertSame('new', $order->status);
        $this->assertSame(123, $order->id);
        $this->assertSame('dpd_standard', $order->service?->slug);
        $this->assertSame('Sender Company', $order->sender?->name);
        $this->assertSame('Receiver Company', $order->receiver?->name);
        $this->assertTrue($order->waybillHasBeenDownloaded);
        $this->assertSame(16.28, $order->price);
        $this->assertCount(2, $order->fees);
        $this->assertSame('base', $order->fees[0]->type);
        $this->assertCount(1, $order->statuses);
        $this->assertSame('Shipment data received', $order->statuses[0]->realName);
        $this->assertSame('/api/v1/order/123', $httpClient->lastRequest?->getUri()->getPath());
    }

    public function testGetStatusesForManyOrdersReturnsTypedCollection(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => new Response(200, [], json_encode([
                'data' => [
                    [
                        'id' => 123,
                        'awb' => '80438360579',
                        'courier' => 'dpd',
                        'status' => 'new',
                        'statuses' => [
                            [
                                'name' => 'new',
                                'real_name' => 'Shipment data received',
                                'created_at' => '2022-12-21T19:43:40.000000Z',
                            ],
                        ],
                        'updated_at' => '2022-12-21T19:43:40.000000Z',
                        'created_at' => '2022-12-21T19:40:00.000000Z',
                    ],
                ],
            ], JSON_THROW_ON_ERROR)),
        );

        $orders = $client->orders()->getStatusesForManyOrders(['80438360579']);
        $firstOrder = $orders->first();

        $this->assertSame(1, $orders->count());
        $this->assertNotNull($firstOrder);
        $this->assertSame(123, $firstOrder->id);
        $this->assertSame('80438360579', $firstOrder->awb);
        $this->assertSame('dpd', $firstOrder->courier);
        $this->assertCount(1, $firstOrder->statuses);
        $this->assertSame('new', $firstOrder->statuses[0]->name);
        $this->assertSame('/api/v1/order/get-statuses-for-many-orders', $httpClient->lastRequest?->getUri()->getPath());
        $this->assertSame('{"awbs":["80438360579"]}', (string) $httpClient->lastRequest?->getBody());
    }

    public function testDownloadWaybillReturnsWaybillDocument(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => new Response(200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename=waybill.pdf',
            ], '%PDF-binary%'),
        );

        $waybill = $client->orders()->downloadWaybill(123);

        $this->assertSame('application/pdf', $waybill->contentType);
        $this->assertStringContainsString('filename=waybill.pdf', (string) $waybill->contentDisposition);
        $this->assertSame('/api/v1/order/123/download-waybill', $httpClient->lastRequest?->getUri()->getPath());
    }

    public function testDeleteOrderReturnsResultMessages(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => new Response(200, [], json_encode([
                'data' => [
                    ['description' => 'Order succesfully canceled'],
                ],
            ], JSON_THROW_ON_ERROR)),
        );

        $result = $client->orders()->deleteOrder(123);

        $this->assertSame(['Order succesfully canceled'], $result->messages);
        $this->assertSame('/api/v1/order/123', $httpClient->lastRequest?->getUri()->getPath());
    }

    public function testGetOrderThrowsValidationExceptionForInvalidId(): void
    {
        [$client, ] = TestClientFactory::create(
            static fn() => new Response(
                422,
                [],
                json_encode(
                    ResponseFixtureFactory::validationError([
                        'id' => ['The selected id is invalid.'],
                    ]),
                    JSON_THROW_ON_ERROR,
                ),
            ),
        );

        try {
            $client->orders()->getOrder(999);
            $this->fail('Expected ValidationException to be thrown.');
        } catch (ValidationException $exception) {
            $this->assertSame('The given data was invalid.', $exception->getMessage());
            $this->assertSame([
                'id' => ['The selected id is invalid.'],
            ], $exception->errors);
        }
    }

    public function testDownloadWaybillThrowsUnexpectedStatusExceptionForServerError(): void
    {
        [$client, ] = TestClientFactory::create(
            static fn() => new Response(
                500,
                [],
                json_encode(ResponseFixtureFactory::serverError(), JSON_THROW_ON_ERROR),
            ),
        );

        $this->expectException(UnexpectedStatusException::class);

        $client->orders()->downloadWaybill(123);
    }
}
