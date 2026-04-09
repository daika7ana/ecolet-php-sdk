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

    private const CONTEXT = 'services';

    #[Group('smoke')]
    public function testGetServicesReturnsNonEmptyCollection(): void
    {
        $services = $this->fetchServices();

        $this->assertInstanceOf(Collection::class, $services);
        $this->assertGreaterThan(0, $services->count());
    }

    #[Group('smoke')]
    public function testGetServicesItemsAreProperlyTyped(): void
    {
        $services = $this->fetchServices();

        foreach ($services as $service) {
            $this->assertInstanceOf(Service::class, $service);
            $this->assertGreaterThan(0, $service->id);
            $this->assertNotSame('', $service->name);
        }
    }

    /**
     * @return Collection<int, Service>
     */
    private function fetchServices(): Collection
    {
        $client = $this->makeAuthenticatedClient(self::CONTEXT);

        return $client->services()->getServices();
    }
}
