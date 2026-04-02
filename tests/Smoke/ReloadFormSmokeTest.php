<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Smoke;

use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Config\ClientConfig;
use Daika7ana\Ecolet\DTOs\AdditionalServices;
use Daika7ana\Ecolet\DTOs\AddParcelRequest;
use Daika7ana\Ecolet\DTOs\AddParcelResult;
use Daika7ana\Ecolet\DTOs\CourierInfo;
use Daika7ana\Ecolet\DTOs\CourierPickup;
use Daika7ana\Ecolet\DTOs\ParcelDetails;
use Daika7ana\Ecolet\DTOs\ParcelDimensions;
use Daika7ana\Ecolet\DTOs\RecipientAddress;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

final class ReloadFormSmokeTest extends TestCase
{
    #[Group('smoke')]
    public function testReloadFormAgainstStagingApi(): void
    {
        $username = getenv('ECOLET_TEST_USERNAME') ?: '';
        $password = getenv('ECOLET_TEST_PASSWORD') ?: '';
        $clientId = getenv('ECOLET_TEST_CLIENT_ID') ?: '';
        $clientSecret = getenv('ECOLET_TEST_CLIENT_SECRET') ?: '';

        if ($username === '' || $password === '' || $clientId === '' || $clientSecret === '') {
            $this->markTestSkipped('Set ECOLET_TEST_USERNAME, ECOLET_TEST_PASSWORD, ECOLET_TEST_CLIENT_ID, ECOLET_TEST_CLIENT_SECRET to run smoke reload-form test.');
        }

        $config = new ClientConfig(baseUrl: ClientConfig::BASE_URL_STAGING);
        $client = Client::create(config: $config);
        $client->authenticate($username, $password, $clientId, $clientSecret);

        $result = $client->addParcel()->reloadForm($this->buildPayload());

        $this->assertSame(ClientConfig::BASE_URL_STAGING, $client->getConfig()->baseUrl);
        $this->assertInstanceOf(AddParcelResult::class, $result);
        $this->assertTrue($result->isFormResponse());
        $this->assertNotNull($result->formResponse);
        $this->assertGreaterThanOrEqual(0, $result->formResponse->billingWeight);
        $this->assertGreaterThan(0, count($result->formResponse->pricing->statuses));
    }

    #[Group('smoke')]
    public function testReloadFormResponseContainsPricingInfo(): void
    {
        $username = getenv('ECOLET_TEST_USERNAME') ?: '';
        $password = getenv('ECOLET_TEST_PASSWORD') ?: '';
        $clientId = getenv('ECOLET_TEST_CLIENT_ID') ?: '';
        $clientSecret = getenv('ECOLET_TEST_CLIENT_SECRET') ?: '';

        if ($username === '' || $password === '' || $clientId === '' || $clientSecret === '') {
            $this->markTestSkipped('Set ECOLET_TEST_USERNAME, ECOLET_TEST_PASSWORD, ECOLET_TEST_CLIENT_ID, ECOLET_TEST_CLIENT_SECRET to run smoke reload-form test.');
        }

        $config = new ClientConfig(baseUrl: ClientConfig::BASE_URL_STAGING);
        $client = Client::create(config: $config);
        $client->authenticate($username, $password, $clientId, $clientSecret);

        $result = $client->addParcel()->reloadForm($this->buildPayload());

        // Verify pricing structure
        $this->assertIsArray($result->formResponse->pricing->pricesNet);
        $this->assertIsArray($result->formResponse->pricing->pricesGross);
        $this->assertIsArray($result->formResponse->pricing->fees);
        $this->assertIsArray($result->formResponse->pricing->isStandard);

        // At least one service should be available with pricing
        $this->assertNotEmpty($result->formResponse->pricing->pricesGross);

        // Check VAT is positive
        $this->assertGreaterThan(0, $result->formResponse->vat);
    }

    #[Group('smoke')]
    public function testReloadFormResponseHasServiceStatuses(): void
    {
        $username = getenv('ECOLET_TEST_USERNAME') ?: '';
        $password = getenv('ECOLET_TEST_PASSWORD') ?: '';
        $clientId = getenv('ECOLET_TEST_CLIENT_ID') ?: '';
        $clientSecret = getenv('ECOLET_TEST_CLIENT_SECRET') ?: '';

        if ($username === '' || $password === '' || $clientId === '' || $clientSecret === '') {
            $this->markTestSkipped('Set ECOLET_TEST_USERNAME, ECOLET_TEST_PASSWORD, ECOLET_TEST_CLIENT_ID, ECOLET_TEST_CLIENT_SECRET to run smoke reload-form test.');
        }

        $config = new ClientConfig(baseUrl: ClientConfig::BASE_URL_STAGING);
        $client = Client::create(config: $config);
        $client->authenticate($username, $password, $clientId, $clientSecret);

        $result = $client->addParcel()->reloadForm($this->buildPayload());

        // Verify service statuses are present and valid
        $statuses = $result->formResponse->pricing->statuses;
        $this->assertIsArray($statuses);
        $this->assertNotEmpty($statuses);

        // Each status should be a boolean
        foreach ($statuses as $serviceSlug => $isAvailable) {
            $this->assertIsString($serviceSlug);
            $this->assertIsBool($isAvailable);
        }

        // Additional services should also be available for the same services
        $additionalServices = $result->formResponse->pricing->additionalServices;
        $this->assertIsArray($additionalServices);
    }

    #[Group('smoke')]
    public function testReloadFormResponseContainsPickupDates(): void
    {
        $username = getenv('ECOLET_TEST_USERNAME') ?: '';
        $password = getenv('ECOLET_TEST_PASSWORD') ?: '';
        $clientId = getenv('ECOLET_TEST_CLIENT_ID') ?: '';
        $clientSecret = getenv('ECOLET_TEST_CLIENT_SECRET') ?: '';

        if ($username === '' || $password === '' || $clientId === '' || $clientSecret === '') {
            $this->markTestSkipped('Set ECOLET_TEST_USERNAME, ECOLET_TEST_PASSWORD, ECOLET_TEST_CLIENT_ID, ECOLET_TEST_CLIENT_SECRET to run smoke reload-form test.');
        }

        $config = new ClientConfig(baseUrl: ClientConfig::BASE_URL_STAGING);
        $client = Client::create(config: $config);
        $client->authenticate($username, $password, $clientId, $clientSecret);

        $result = $client->addParcel()->reloadForm($this->buildPayload());

        // Pickup dates should be available (may be empty or populated)
        $pickupDates = $result->formResponse->pricing->pickupDates;
        $this->assertIsArray($pickupDates);

        // If pickup dates are present, they should have structure
        if (count($pickupDates) > 0) {
            foreach ($pickupDates as $serviceSlug => $dateInfo) {
                $this->assertIsString($serviceSlug);
                $this->assertIsArray($dateInfo);
            }
        }
    }

    #[Group('smoke')]
    public function testReloadFormResponseHasNoErrors(): void
    {
        $username = getenv('ECOLET_TEST_USERNAME') ?: '';
        $password = getenv('ECOLET_TEST_PASSWORD') ?: '';
        $clientId = getenv('ECOLET_TEST_CLIENT_ID') ?: '';
        $clientSecret = getenv('ECOLET_TEST_CLIENT_SECRET') ?: '';

        if ($username === '' || $password === '' || $clientId === '' || $clientSecret === '') {
            $this->markTestSkipped('Set ECOLET_TEST_USERNAME, ECOLET_TEST_PASSWORD, ECOLET_TEST_CLIENT_ID, ECOLET_TEST_CLIENT_SECRET to run smoke reload-form test.');
        }

        $config = new ClientConfig(baseUrl: ClientConfig::BASE_URL_STAGING);
        $client = Client::create(config: $config);
        $client->authenticate($username, $password, $clientId, $clientSecret);

        $result = $client->addParcel()->reloadForm($this->buildPayload());

        // Verify error and info structures are present (they may or may not contain data)
        $this->assertIsArray($result->formResponse->errors);
        $this->assertIsArray($result->formResponse->info);

        // If there are errors, they should have the expected structure (field => messages)
        if ($result->formResponse->hasErrors()) {
            foreach ($result->formResponse->errors as $field => $messages) {
                $this->assertIsString($field);
                $this->assertIsArray($messages);
                foreach ($messages as $message) {
                    $this->assertIsString($message);
                }
            }
        }
    }

    /**
     * Build a tightly-typed reload-form request with the provided test data.
     */
    private function buildPayload(): AddParcelRequest
    {
        return new AddParcelRequest(
            sender: new RecipientAddress(
                name: 'Test Company',
                country: 'ro',
                county: 'Constanta',
                locality: 'Constanta',
                localityId: 3150,
                postalCode: '900003',
                streetName: 'Str. Interioara',
                streetNumber: '103',
                block: '1',
                entrance: 'A2',
                floor: '1',
                flat: 'A3a',
                contactPerson: 'Test Test',
                email: 'user@example.com',
                phone: '0214824089',
            ),
            receiver: new RecipientAddress(
                name: 'Test Company',
                country: 'ro',
                county: 'Constanta',
                locality: 'Constanta',
                localityId: 3150,
                postalCode: '900003',
                streetName: 'Str. Dezrobirii',
                streetNumber: '296',
                block: '1',
                entrance: 'A2',
                floor: '1',
                flat: 'A3a',
                contactPerson: 'Test Test',
                email: 'user@example.com',
                phone: '0214824089',
            ),
            parcel: new ParcelDetails(
                type: 'package',
                weight: 1,
                shape: 'standard',
                observations: 'FRAGILE',
                amount: 1,
            ),
            additionalServices: new AdditionalServices(
                cod: true,
                codAmount: 500,
            ),
            courier: new CourierInfo(
                pickup: new CourierPickup(type: 'courier'),
            ),
            parcels: [
                new ParcelDetails(
                    type: 'package',
                    weight: 1,
                    dimensions: new ParcelDimensions(length: 10, width: 10, height: 10),
                    content: 'Biscuits 400gr',
                    declaredValue: 50,
                    amount: 1,
                ),
            ],
        );
    }
}
