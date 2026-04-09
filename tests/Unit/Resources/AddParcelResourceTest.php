<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\Resources;

use Daika7ana\Ecolet\DTOs\AddParcel\CourierInfo;
use Daika7ana\Ecolet\DTOs\AddParcel\CourierPickup;
use Daika7ana\Ecolet\Enums\CourierPickupType;
use Daika7ana\Ecolet\Exceptions\UnexpectedStatusException;
use Daika7ana\Ecolet\Exceptions\ValidationException;
use Daika7ana\Ecolet\Tests\Support\AddParcelRequestFactory;
use Daika7ana\Ecolet\Tests\Support\ResponseFixtureFactory;
use Daika7ana\Ecolet\Tests\Support\TestClientFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class AddParcelResourceTest extends TestCase
{
    public function testSendOrderSupportsSingleParcelPayload(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => new Response(
                200,
                [],
                json_encode(ResponseFixtureFactory::orderToSendId(42), JSON_THROW_ON_ERROR),
            ),
        );

        $payload = AddParcelRequestFactory::make(
            parcel: AddParcelRequestFactory::parcel(1500),
            parcels: AddParcelRequestFactory::parcels(1500),
        );

        $result = $client->addParcel()->sendOrder($payload);

        $this->assertTrue($result->isOrderResponse());
        $this->assertSame(42, $result->orderToSendId);
        $this->assertSame('/api/v2/add-parcel/send-order', $httpClient->lastRequest?->getUri()->getPath());

        $requestBody = (string) $httpClient->lastRequest?->getBody();
        $decoded = json_decode($requestBody, true, flags: JSON_THROW_ON_ERROR);
        $this->assertCount(1, $decoded['parcels']);
    }

    public function testSendOrderIncludesCourierServiceAndPickupSchedule(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => new Response(
                200,
                [],
                json_encode(ResponseFixtureFactory::orderToSendId(42), JSON_THROW_ON_ERROR),
            ),
        );

        $payload = AddParcelRequestFactory::make(
            parcel: AddParcelRequestFactory::parcel(1500),
            courier: AddParcelRequestFactory::courierPickup(
                day: 'Friday',
                date: '2026-04-10',
                time: '10:00',
                service: 'dpd_standard',
            ),
            parcels: AddParcelRequestFactory::parcels(1500),
        );

        $client->addParcel()->sendOrder($payload);

        $decoded = json_decode((string) $httpClient->lastRequest?->getBody(), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('dpd_standard', $decoded['courier']['service']);
        $this->assertSame('Friday', $decoded['courier']['pickup']['day']);
        $this->assertSame('2026-04-10', $decoded['courier']['pickup']['date']);
        $this->assertSame('10:00', $decoded['courier']['pickup']['time']);
    }

    public function testSaveOrderSupportsMultipackPayload(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => new Response(
                200,
                [],
                json_encode(ResponseFixtureFactory::orderToSendId(99), JSON_THROW_ON_ERROR),
            ),
        );

        $payload = AddParcelRequestFactory::make(
            parcel: AddParcelRequestFactory::parcel(2000),
            parcels: AddParcelRequestFactory::parcels(1000, 2000),
        );

        $result = $client->addParcel()->saveOrderToSend($payload);

        $this->assertTrue($result->isOrderResponse());
        $this->assertSame(99, $result->orderToSendId);
        $this->assertSame('/api/v2/add-parcel/save-order-to-send', $httpClient->lastRequest?->getUri()->getPath());

        $requestBody = (string) $httpClient->lastRequest?->getBody();
        $decoded = json_decode($requestBody, true, flags: JSON_THROW_ON_ERROR);
        $this->assertCount(2, $decoded['parcels']);
    }
    public function testAllOperationsReturnTypedResult(): void
    {
        [$client, ] = TestClientFactory::create(
            static fn() => new Response(
                200,
                [],
                json_encode(ResponseFixtureFactory::reloadForm(), JSON_THROW_ON_ERROR),
            ),
        );

        $payload = AddParcelRequestFactory::make(
            parcel: AddParcelRequestFactory::parcel(1100),
            parcels: AddParcelRequestFactory::parcels(1100),
        );

        $reload = $client->addParcel()->reloadForm($payload);

        $this->assertTrue($reload->isFormResponse());
        $this->assertFalse($reload->isOrderResponse());
        $this->assertNotNull($reload->formResponse);
        $this->assertSame(1, $reload->formResponse->billingWeight);
        $this->assertSame(19, $reload->formResponse->vat);
        $this->assertTrue($reload->formResponse->pricing->statuses['dpd_standard']);
    }
    public function testReloadFormHandlesValidationErrors(): void
    {
        [$client, ] = TestClientFactory::create(
            static fn() => new Response(
                200,
                [],
                json_encode(
                    ResponseFixtureFactory::reloadForm(
                        errors: [
                            'sender.name' => ['The sender name is required.'],
                            'receiver.postal_code' => ['The postal code is invalid.'],
                        ],
                        overrides: [
                            'form' => [
                                'statuses' => [],
                                'additional_services' => [],
                                'prices_net' => [],
                                'prices_gross' => [],
                                'is_standard' => [],
                                'billing_weight' => 0,
                            ],
                        ],
                    ),
                    JSON_THROW_ON_ERROR,
                ),
            ),
        );

        $payload = AddParcelRequestFactory::make();

        $result = $client->addParcel()->reloadForm($payload);

        $this->assertTrue($result->isFormResponse());
        $this->assertNotNull($result->formResponse);
        $this->assertTrue($result->formResponse->hasErrors());

        $errorMessages = $result->formResponse->getErrorMessages();
        $this->assertCount(2, $errorMessages);
        $this->assertContains('The sender name is required.', $errorMessages);
        $this->assertContains('The postal code is invalid.', $errorMessages);
    }

    public function testReloadFormIgnoresEmptyErrorBuckets(): void
    {
        [$client, ] = TestClientFactory::create(
            static fn() => new Response(
                200,
                [],
                json_encode(
                    ResponseFixtureFactory::reloadForm(
                        errors: [
                            'dpd_standard' => [],
                            'gls_standard' => [],
                        ],
                    ),
                    JSON_THROW_ON_ERROR,
                ),
            ),
        );

        $payload = AddParcelRequestFactory::make();

        $result = $client->addParcel()->reloadForm($payload);

        $this->assertTrue($result->isFormResponse());
        $this->assertNotNull($result->formResponse);
        $this->assertFalse($result->formResponse->hasErrors());
        $this->assertSame([], $result->formResponse->getErrorMessages());
    }

    public function testSendOrderThrowsValidationExceptionForInvalidPayload(): void
    {
        [$client, ] = TestClientFactory::create(
            static fn() => new Response(
                422,
                [],
                json_encode(
                    ResponseFixtureFactory::validationError([
                        'courier.pickup.day' => ['The pickup day field is required when pickup type is courier.'],
                        'courier.pickup.time' => ['The pickup time field is required when pickup type is courier.'],
                    ]),
                    JSON_THROW_ON_ERROR,
                ),
            ),
        );

        $payload = AddParcelRequestFactory::make(
            courier: new CourierInfo(
                pickup: new CourierPickup(type: CourierPickupType::Courier),
            ),
        );

        try {
            $client->addParcel()->sendOrder($payload);
            $this->fail('Expected ValidationException to be thrown.');
        } catch (ValidationException $exception) {
            $this->assertSame('The given data was invalid.', $exception->getMessage());
            $this->assertSame([
                'courier.pickup.day' => ['The pickup day field is required when pickup type is courier.'],
                'courier.pickup.time' => ['The pickup time field is required when pickup type is courier.'],
            ], $exception->errors);
        }
    }

    public function testSaveOrderToSendThrowsValidationExceptionForGeneralError(): void
    {
        [$client, ] = TestClientFactory::create(
            static fn() => new Response(
                422,
                [],
                json_encode(ResponseFixtureFactory::generalError('Service unavailable.'), JSON_THROW_ON_ERROR),
            ),
        );

        $payload = AddParcelRequestFactory::make();

        try {
            $client->addParcel()->saveOrderToSend($payload);
            $this->fail('Expected ValidationException to be thrown.');
        } catch (ValidationException $exception) {
            $this->assertSame('Service unavailable.', $exception->getMessage());
            $this->assertSame([], $exception->errors);
        }
    }

    public function testSendOrderThrowsUnexpectedStatusExceptionForServerError(): void
    {
        [$client, ] = TestClientFactory::create(
            static fn() => new Response(
                500,
                [],
                json_encode(ResponseFixtureFactory::serverError(), JSON_THROW_ON_ERROR),
            ),
        );

        $payload = AddParcelRequestFactory::make();

        $this->expectException(UnexpectedStatusException::class);

        $client->addParcel()->sendOrder($payload);
    }
}
