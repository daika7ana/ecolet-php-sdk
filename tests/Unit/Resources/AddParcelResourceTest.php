<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\Resources;

use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Config\ClientConfig;
use Daika7ana\Ecolet\DTOs\AdditionalServices;
use Daika7ana\Ecolet\DTOs\AddParcelRequest;
use Daika7ana\Ecolet\DTOs\CourierInfo;
use Daika7ana\Ecolet\DTOs\CourierPickup;
use Daika7ana\Ecolet\DTOs\ParcelDetails;
use Daika7ana\Ecolet\DTOs\RecipientAddress;
use Daika7ana\Ecolet\Tests\Support\FakeHttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class AddParcelResourceTest extends TestCase
{
    public function testSendOrderSupportsSingleParcelPayload(): void
    {
        $httpClient = new FakeHttpClient(
            static fn() => new Response(200, [], json_encode(['order_to_send_id' => 42], JSON_THROW_ON_ERROR)),
        );
        $factory = new HttpFactory();

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: ClientConfig::fromEnvironment(),
        );

        $sender = new RecipientAddress(
            name: 'Test Company',
            country: 'ro',
            locality: 'Bucuresti',
            localityId: 323,
            postalCode: '011318',
            streetName: 'Test Street',
            streetNumber: '123',
            contactPerson: 'John Doe',
            email: 'john@example.com',
            phone: '0123456789',
        );

        $receiver = new RecipientAddress(
            name: 'Test Recipient',
            country: 'ro',
            locality: 'Bucuresti',
            localityId: 323,
            postalCode: '011318',
            streetName: 'Recipient Street',
            streetNumber: '456',
            contactPerson: 'Jane Doe',
            email: 'jane@example.com',
            phone: '0987654321',
        );

        $parcel = new ParcelDetails(
            type: 'package',
            weight: 1500,
        );

        $payload = new AddParcelRequest(
            sender: $sender,
            receiver: $receiver,
            parcel: $parcel,
            additionalServices: new AdditionalServices(),
            courier: new CourierInfo(
                pickup: new CourierPickup(type: 'self'),
            ),
            parcels: [
                new ParcelDetails(type: 'package', weight: 1500),
            ],
        );

        $result = $client->addParcel()->sendOrder($payload);

        $this->assertTrue($result->isOrderResponse());
        $this->assertSame(42, $result->orderToSendId);
        $this->assertSame('/api/v2/add-parcel/send-order', $httpClient->lastRequest?->getUri()->getPath());

        $requestBody = (string) $httpClient->lastRequest?->getBody();
        $decoded = json_decode($requestBody, true, flags: JSON_THROW_ON_ERROR);
        $this->assertCount(1, $decoded['parcels']);
    }

    public function testSaveOrderSupportsMultipackPayload(): void
    {
        $httpClient = new FakeHttpClient(
            static fn() => new Response(200, [], json_encode(['order_to_send_id' => 99], JSON_THROW_ON_ERROR)),
        );
        $factory = new HttpFactory();

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: ClientConfig::fromEnvironment(),
        );

        $sender = new RecipientAddress(
            name: 'Test Company',
            country: 'ro',
            locality: 'Bucuresti',
            localityId: 323,
            postalCode: '011318',
            streetName: 'Test Street',
            streetNumber: '123',
            contactPerson: 'John Doe',
            email: 'john@example.com',
            phone: '0123456789',
        );

        $receiver = new RecipientAddress(
            name: 'Test Recipient',
            country: 'ro',
            locality: 'Bucuresti',
            localityId: 323,
            postalCode: '011318',
            streetName: 'Recipient Street',
            streetNumber: '456',
            contactPerson: 'Jane Doe',
            email: 'jane@example.com',
            phone: '0987654321',
        );

        $payload = new AddParcelRequest(
            sender: $sender,
            receiver: $receiver,
            parcel: new ParcelDetails(type: 'package', weight: 2000),
            additionalServices: new AdditionalServices(),
            courier: new CourierInfo(
                pickup: new CourierPickup(type: 'self'),
            ),
            parcels: [
                new ParcelDetails(type: 'package', weight: 1000),
                new ParcelDetails(type: 'package', weight: 2000),
            ],
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
        // For reload-form: returns form data with pricing
        $formResponse = [
            'form' => [
                'statuses' => ['dpd_standard' => true],
                'additional_services' => ['dpd_standard' => ['cod' => true]],
                'pickup_dates' => [],
                'prices_net' => ['dpd_standard' => '16.28'],
                'prices_gross' => ['dpd_standard' => '19.37'],
                'fees' => [],
                'is_standard' => ['dpd_standard' => true],
                'billing_weight' => 1,
                'vat' => 19,
                'info' => [],
                'errors' => [],
            ],
        ];

        $httpClient = new FakeHttpClient(
            static fn() => new Response(200, [], json_encode($formResponse, JSON_THROW_ON_ERROR)),
        );
        $factory = new HttpFactory();

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: ClientConfig::fromEnvironment(),
        );

        $sender = new RecipientAddress(
            name: 'Test Company',
            country: 'ro',
            locality: 'Bucuresti',
            localityId: 323,
            postalCode: '011318',
            streetName: 'Test Street',
            streetNumber: '123',
            contactPerson: 'John Doe',
            email: 'john@example.com',
            phone: '0123456789',
        );

        $receiver = new RecipientAddress(
            name: 'Test Recipient',
            country: 'ro',
            locality: 'Bucuresti',
            localityId: 323,
            postalCode: '011318',
            streetName: 'Recipient Street',
            streetNumber: '456',
            contactPerson: 'Jane Doe',
            email: 'jane@example.com',
            phone: '0987654321',
        );

        $payload = new AddParcelRequest(
            sender: $sender,
            receiver: $receiver,
            parcel: new ParcelDetails(type: 'package', weight: 1100),
            additionalServices: new AdditionalServices(),
            courier: new CourierInfo(
                pickup: new CourierPickup(type: 'self'),
            ),
            parcels: [
                new ParcelDetails(type: 'package', weight: 1100),
            ],
        );

        $reload = $client->addParcel()->reloadForm($payload);

        // Verify typed response access for reload-form
        $this->assertTrue($reload->isFormResponse());
        $this->assertFalse($reload->isOrderResponse());
        $this->assertNotNull($reload->formResponse);
        $this->assertSame(1, $reload->formResponse->billingWeight);
        $this->assertSame(19, $reload->formResponse->vat);
        $this->assertTrue($reload->formResponse->pricing->statuses['dpd_standard']);
    }


    public function testReloadFormHandlesValidationErrors(): void
    {
        $errorResponse = [
            'form' => [
                'statuses' => [],
                'additional_services' => [],
                'pickup_dates' => [],
                'prices_net' => [],
                'prices_gross' => [],
                'fees' => [],
                'is_standard' => [],
                'billing_weight' => 0,
                'vat' => 19,
                'info' => [],
                'errors' => [
                    'sender.name' => ['The sender name is required.'],
                    'receiver.postal_code' => ['The postal code is invalid.'],
                ],
            ],
        ];

        $httpClient = new FakeHttpClient(
            static fn() => new Response(200, [], json_encode($errorResponse, JSON_THROW_ON_ERROR)),
        );
        $factory = new HttpFactory();

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: ClientConfig::fromEnvironment(),
        );

        $sender = new RecipientAddress(
            name: 'Test Company',
            country: 'ro',
            locality: 'Bucuresti',
            localityId: 323,
            postalCode: '011318',
            streetName: 'Test Street',
            streetNumber: '123',
            contactPerson: 'John Doe',
            email: 'john@example.com',
            phone: '0123456789',
        );

        $receiver = new RecipientAddress(
            name: 'Test Recipient',
            country: 'ro',
            locality: 'Bucuresti',
            localityId: 323,
            postalCode: '011318',
            streetName: 'Recipient Street',
            streetNumber: '456',
            contactPerson: 'Jane Doe',
            email: 'jane@example.com',
            phone: '0987654321',
        );

        $payload = new AddParcelRequest(
            sender: $sender,
            receiver: $receiver,
            parcel: new ParcelDetails(type: 'package', weight: 1000),
            additionalServices: new AdditionalServices(),
            courier: new CourierInfo(
                pickup: new CourierPickup(type: 'self'),
            ),
            parcels: [
                new ParcelDetails(type: 'package', weight: 1000),
            ],
        );

        $result = $client->addParcel()->reloadForm($payload);

        // Verify form response with errors is properly parsed
        $this->assertTrue($result->isFormResponse());
        $this->assertNotNull($result->formResponse);
        $this->assertTrue($result->formResponse->hasErrors());

        $errorMessages = $result->formResponse->getErrorMessages();
        $this->assertCount(2, $errorMessages);
        $this->assertContains('The sender name is required.', $errorMessages);
        $this->assertContains('The postal code is invalid.', $errorMessages);
    }
}
