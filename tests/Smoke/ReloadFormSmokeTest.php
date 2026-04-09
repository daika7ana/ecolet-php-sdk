<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Smoke;

use Daika7ana\Ecolet\Config\ClientConfig;
use Daika7ana\Ecolet\DTOs\AddParcel\AddParcelResult;
use Daika7ana\Ecolet\Tests\Smoke\Concerns\InteractsWithAuthenticatedSmokeClient;
use Daika7ana\Ecolet\Tests\Smoke\Support\AddParcelSmokePayloadFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

final class ReloadFormSmokeTest extends TestCase
{
    use InteractsWithAuthenticatedSmokeClient;

    #[Group('smoke')]
    public function testReloadFormAgainstStagingApi(): void
    {
        $client = $this->makeAuthenticatedClient('reload-form');

        $result = $client->addParcel()->reloadForm(AddParcelSmokePayloadFactory::workflowPayload());

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
        $client = $this->makeAuthenticatedClient('reload-form');

        $result = $client->addParcel()->reloadForm(AddParcelSmokePayloadFactory::workflowPayload());

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
        $client = $this->makeAuthenticatedClient('reload-form');

        $result = $client->addParcel()->reloadForm(AddParcelSmokePayloadFactory::workflowPayload());

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
        $client = $this->makeAuthenticatedClient('reload-form');

        $result = $client->addParcel()->reloadForm(AddParcelSmokePayloadFactory::workflowPayload());

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
        $client = $this->makeAuthenticatedClient('reload-form');

        $result = $client->addParcel()->reloadForm(AddParcelSmokePayloadFactory::workflowPayload());

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
}
