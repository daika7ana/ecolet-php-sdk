<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\Resources;

use Daika7ana\Ecolet\Exceptions\UnexpectedStatusException;
use Daika7ana\Ecolet\Exceptions\ValidationException;
use Daika7ana\Ecolet\Tests\Support\ResponseFixtureFactory;
use Daika7ana\Ecolet\Tests\Support\TestClientFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class OrderToSendResourceTest extends TestCase
{
    public function testGetOrderToSendReturnsTypedDto(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => new Response(200, [], json_encode([
                'order_to_send' => [
                    'id' => 321,
                    'status' => 'ordered',
                    'error' => null,
                    'order_id' => 654,
                    'source' => 'external api',
                    'source_order_id' => 777,
                    'created_at' => '2022-12-21T19:43:40.000000Z',
                    'imported_order_created_at' => '2022-12-21T19:40:00.000000Z',
                    'order' => [
                        'data' => [
                            'sender' => [
                                'name' => 'Sender Company',
                                'country' => 'ro',
                                'county' => 'B',
                                'locality' => 'Bucuresti',
                                'locality_id' => 13751,
                                'postal_code' => '011318',
                                'street_name' => 'Bucuresti-Ploiesti',
                                'street_number' => '10',
                                'contact_person' => 'John Doe',
                                'email' => 'sender@example.test',
                                'phone' => '0712345678',
                                'has_map_point' => false,
                            ],
                            'receiver' => [
                                'name' => 'Receiver Company',
                                'country' => 'ro',
                                'county' => 'B',
                                'locality' => 'Bucuresti',
                                'locality_id' => 13751,
                                'postal_code' => '011318',
                                'street_name' => 'Unirii',
                                'street_number' => '15',
                                'contact_person' => 'Jane Doe',
                                'email' => 'receiver@example.test',
                                'phone' => '0799999999',
                                'has_map_point' => false,
                            ],
                            'parcel' => [
                                'type' => 'package',
                                'weight' => 1,
                                'amount' => 1,
                                'content' => 'Books',
                            ],
                            'parcels' => [[
                                'type' => 'package',
                                'weight' => 1,
                                'amount' => 1,
                                'content' => 'Books',
                            ]],
                            'additional_services' => [
                                'cod' => ['status' => false],
                                'open_package' => ['status' => false],
                                'rod' => ['status' => false],
                                'rop' => ['status' => false],
                                'saturday_delivery' => ['status' => false],
                                'sms_notify' => ['status' => false],
                                'swap' => ['status' => false],
                                'epod' => ['status' => false],
                            ],
                            'courier' => [
                                'service' => 'dpd_standard',
                                'pickup' => [
                                    'type' => 'courier',
                                    'day' => 'Friday',
                                    'date' => '2022-01-21',
                                    'time' => '13:00',
                                ],
                            ],
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR)),
        );

        $orderToSend = $client->ordersToSend()->getOrderToSend(321);

        $this->assertSame(321, $orderToSend->id);
        $this->assertSame('ordered', $orderToSend->status);
        $this->assertSame(654, $orderToSend->orderId);
        $this->assertSame('external api', $orderToSend->source);
        $this->assertNotNull($orderToSend->order);
        $this->assertSame('Sender Company', $orderToSend->order?->sender->name);
        $this->assertSame('/api/v1/order-to-send/321', $httpClient->lastRequest?->getUri()->getPath());
    }

    public function testGetOrderToSendParsesErrorStatusWithoutOrderPayload(): void
    {
        [$client, ] = TestClientFactory::create(
            static fn() => new Response(200, [], json_encode([
                'order_to_send' => [
                    'id' => 999,
                    'status' => 'error',
                    'error' => 'Courier service unavailable.',
                    'order_id' => null,
                ],
            ], JSON_THROW_ON_ERROR)),
        );

        $orderToSend = $client->ordersToSend()->getOrderToSend(999);

        $this->assertSame(999, $orderToSend->id);
        $this->assertSame('error', $orderToSend->status);
        $this->assertSame('Courier service unavailable.', $orderToSend->error);
        $this->assertNull($orderToSend->orderId);
        $this->assertNull($orderToSend->order);
    }

    public function testGetOrderToSendThrowsValidationExceptionForInvalidId(): void
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
            $client->ordersToSend()->getOrderToSend(999);
            $this->fail('Expected ValidationException to be thrown.');
        } catch (ValidationException $exception) {
            $this->assertSame('The given data was invalid.', $exception->getMessage());
            $this->assertSame([
                'id' => ['The selected id is invalid.'],
            ], $exception->errors);
        }
    }

    public function testGetOrderToSendThrowsUnexpectedStatusExceptionForServerError(): void
    {
        [$client, ] = TestClientFactory::create(
            static fn() => new Response(
                500,
                [],
                json_encode(ResponseFixtureFactory::serverError(), JSON_THROW_ON_ERROR),
            ),
        );

        $this->expectException(UnexpectedStatusException::class);

        $client->ordersToSend()->getOrderToSend(999);
    }
}
