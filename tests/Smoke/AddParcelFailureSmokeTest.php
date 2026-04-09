<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Smoke;

use Daika7ana\Ecolet\Exceptions\UnexpectedStatusException;
use Daika7ana\Ecolet\Exceptions\ValidationException;
use Daika7ana\Ecolet\Tests\Smoke\Concerns\InteractsWithAuthenticatedSmokeClient;
use Daika7ana\Ecolet\Tests\Smoke\Support\AddParcelSmokePayloadFactory;
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

        $this->assertValidationErrorKeys(
            static fn() => $client->addParcel()->reloadForm(AddParcelSmokePayloadFactory::malformedPayload()),
            [
                'parcel.shape',
                'parcels.0.dimensions.length',
                'parcels.0.dimensions.width',
                'parcels.0.dimensions.height',
            ],
        );
    }

    #[Group('smoke')]
    public function testSendOrderRejectsMalformedPayloadOnStaging(): void
    {
        $client = $this->makeAuthenticatedClient('add-parcel-send-failure');

        $this->assertValidationErrorKeys(
            static fn() => $client->addParcel()->sendOrder(AddParcelSmokePayloadFactory::malformedPayload()),
            [
                'sender.name',
                'sender.email',
                'receiver.email',
                'courier.service',
                'parcel.shape',
            ],
        );
    }

    #[Group('smoke')]
    public function testSaveOrderToSendRejectsMalformedPayloadOnStaging(): void
    {
        $client = $this->makeAuthenticatedClient('add-parcel-save-failure');

        $this->assertValidationErrorKeys(
            static fn() => $client->addParcel()->saveOrderToSend(AddParcelSmokePayloadFactory::malformedPayload()),
            [
                'parcel.shape',
                'parcels.0.dimensions.length',
                'parcels.0.dimensions.width',
                'parcels.0.dimensions.height',
            ],
        );
    }

    #[Group('smoke')]
    public function testGetOrderToSendRejectsUnknownIdOnStaging(): void
    {
        $client = $this->makeAuthenticatedClient('order-to-send-not-found');

        $this->assertNotFound(
            static fn() => $client->ordersToSend()->getOrderToSend(self::NONEXISTENT_REMOTE_ID),
        );
    }

    #[Group('smoke')]
    public function testGetOrderRejectsUnknownIdOnStaging(): void
    {
        $client = $this->makeAuthenticatedClient('order-not-found');

        $this->assertNotFound(
            static fn() => $client->orders()->getOrder(self::NONEXISTENT_REMOTE_ID),
        );
    }

    /**
     * @param callable(): mixed $operation
     * @param array<int, string> $expectedKeys
     */
    private function assertValidationErrorKeys(callable $operation, array $expectedKeys): void
    {
        try {
            $operation();
            $this->fail('Expected ValidationException to be thrown.');
        } catch (ValidationException $exception) {
            foreach ($expectedKeys as $key) {
                $this->assertArrayHasKey($key, $exception->errors);
            }
        }
    }

    /**
     * @param callable(): mixed $operation
     */
    private function assertNotFound(callable $operation): void
    {
        try {
            $operation();
            $this->fail('Expected UnexpectedStatusException to be thrown.');
        } catch (UnexpectedStatusException $exception) {
            $this->assertSame(404, $exception->response->getStatusCode());
        }
    }
}
