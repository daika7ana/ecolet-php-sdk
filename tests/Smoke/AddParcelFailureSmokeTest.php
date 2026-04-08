<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Smoke;

use Daika7ana\Ecolet\DTOs\AddParcel\AdditionalServices;
use Daika7ana\Ecolet\DTOs\AddParcel\AddParcelRequest;
use Daika7ana\Ecolet\DTOs\AddParcel\CourierInfo;
use Daika7ana\Ecolet\DTOs\AddParcel\CourierPickup;
use Daika7ana\Ecolet\DTOs\AddParcel\ParcelDetails;
use Daika7ana\Ecolet\DTOs\AddParcel\RecipientAddress;
use Daika7ana\Ecolet\Enums\CourierPickupType;
use Daika7ana\Ecolet\Enums\ParcelType;
use Daika7ana\Ecolet\Exceptions\UnexpectedStatusException;
use Daika7ana\Ecolet\Exceptions\ValidationException;
use Daika7ana\Ecolet\Tests\Smoke\Concerns\InteractsWithAuthenticatedSmokeClient;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

final class AddParcelFailureSmokeTest extends TestCase
{
    use InteractsWithAuthenticatedSmokeClient;

    private const NONEXISTENT_REMOTE_ID = 999999999;

    #[Group('smoke')]
    public function testReloadFormRejectsMalformedPayloadOnStaging(): void
    {
        $client = $this->makeAuthenticatedClient('add-parcel-reload-failure');

        try {
            $client->addParcel()->reloadForm($this->buildMalformedPayload());
            $this->fail('Expected ValidationException to be thrown.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('parcel.shape', $exception->errors);
            $this->assertArrayHasKey('parcels.0.dimensions.length', $exception->errors);
            $this->assertArrayHasKey('parcels.0.dimensions.width', $exception->errors);
            $this->assertArrayHasKey('parcels.0.dimensions.height', $exception->errors);
        }
    }

    #[Group('smoke')]
    public function testSendOrderRejectsMalformedPayloadOnStaging(): void
    {
        $client = $this->makeAuthenticatedClient('add-parcel-send-failure');

        try {
            $client->addParcel()->sendOrder($this->buildMalformedPayload());
            $this->fail('Expected ValidationException to be thrown.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('sender.name', $exception->errors);
            $this->assertArrayHasKey('sender.email', $exception->errors);
            $this->assertArrayHasKey('receiver.email', $exception->errors);
            $this->assertArrayHasKey('courier.service', $exception->errors);
            $this->assertArrayHasKey('parcel.shape', $exception->errors);
        }
    }

    #[Group('smoke')]
    public function testSaveOrderToSendRejectsMalformedPayloadOnStaging(): void
    {
        $client = $this->makeAuthenticatedClient('add-parcel-save-failure');

        try {
            $client->addParcel()->saveOrderToSend($this->buildMalformedPayload());
            $this->fail('Expected ValidationException to be thrown.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('parcel.shape', $exception->errors);
            $this->assertArrayHasKey('parcels.0.dimensions.length', $exception->errors);
            $this->assertArrayHasKey('parcels.0.dimensions.width', $exception->errors);
            $this->assertArrayHasKey('parcels.0.dimensions.height', $exception->errors);
        }
    }

    #[Group('smoke')]
    public function testGetOrderToSendRejectsUnknownIdOnStaging(): void
    {
        $client = $this->makeAuthenticatedClient('order-to-send-not-found');

        try {
            $client->ordersToSend()->getOrderToSend(self::NONEXISTENT_REMOTE_ID);
            $this->fail('Expected UnexpectedStatusException to be thrown.');
        } catch (UnexpectedStatusException $exception) {
            $this->assertSame(404, $exception->response->getStatusCode());
        }
    }

    #[Group('smoke')]
    public function testGetOrderRejectsUnknownIdOnStaging(): void
    {
        $client = $this->makeAuthenticatedClient('order-not-found');

        try {
            $client->orders()->getOrder(self::NONEXISTENT_REMOTE_ID);
            $this->fail('Expected UnexpectedStatusException to be thrown.');
        } catch (UnexpectedStatusException $exception) {
            $this->assertSame(404, $exception->response->getStatusCode());
        }
    }

    private function buildMalformedPayload(): AddParcelRequest
    {
        return new AddParcelRequest(
            sender: new RecipientAddress(
                name: 'x',
                country: 'ro',
                county: 'Constanta',
                locality: 'Constanta',
                localityId: 3150,
                postalCode: '1',
                streetName: 'x',
                streetNumber: '1',
                contactPerson: 'x',
                email: 'bad-email',
                phone: '1',
            ),
            receiver: new RecipientAddress(
                name: 'x',
                country: 'ro',
                county: 'Constanta',
                locality: 'Constanta',
                localityId: 3150,
                postalCode: '1',
                streetName: 'x',
                streetNumber: '1',
                contactPerson: 'x',
                email: 'bad-email',
                phone: '1',
            ),
            parcel: new ParcelDetails(
                type: ParcelType::Package,
                weight: 1,
            ),
            additionalServices: new AdditionalServices(),
            courier: new CourierInfo(
                pickup: new CourierPickup(type: CourierPickupType::Self),
            ),
            parcels: [
                new ParcelDetails(
                    type: ParcelType::Package,
                    weight: 1,
                ),
            ],
        );
    }
}
