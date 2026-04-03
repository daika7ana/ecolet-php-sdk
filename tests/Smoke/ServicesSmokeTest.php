<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Smoke;

use Daika7ana\Ecolet\DTOs\Common\Collection;
use Daika7ana\Ecolet\DTOs\Services\Service;
use Daika7ana\Ecolet\Tests\Smoke\Concerns\InteractsWithAuthenticatedSmokeClient;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

final class ServicesSmokeTest extends TestCase
{
    use InteractsWithAuthenticatedSmokeClient;

    #[Group('smoke')]
    public function testGetServicesReturnsNonEmptyCollection(): void
    {
        $client = $this->makeAuthenticatedClient('services');

        $services = $client->services()->getServices();

        $this->assertInstanceOf(Collection::class, $services);
        $this->assertNotEmpty($services->items);
    }

    #[Group('smoke')]
    public function testGetServicesItemsAreProperlyTyped(): void
    {
        $client = $this->makeAuthenticatedClient('services');

        $services = $client->services()->getServices();

        foreach ($services->items as $service) {
            $this->assertInstanceOf(Service::class, $service);
            $this->assertGreaterThan(0, $service->id);
            $this->assertNotSame('', $service->name);
        }
    }
}
