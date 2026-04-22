<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\Resources;

use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Config\ClientConfig;
use Daika7ana\Ecolet\DTOs\Services\Service;
use Daika7ana\Ecolet\Tests\Support\FakeHttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ServicesResourceTest extends TestCase
{
    public function testGetServicesReturnsCollection(): void
    {
        $httpClient = new FakeHttpClient(
            static fn() => new Response(200, [], json_encode([
                'services' => [
                    [
                        'id' => 1,
                        'slug' => 'dpd_standard',
                        'name' => 'Standard',
                        'full_name' => 'DPD Standard',
                        'code_name' => 'Standard',
                        'status' => true,
                        'notes' => '<p>Some note</p>',
                        'is_new' => false,
                        'is_promo' => true,
                        'logo_url' => 'https://panel.ecolet.ro/images/couriers/dpd_classic.png',
                        'courier' => [
                            'id' => 2,
                            'slug' => 'dpd',
                            'name' => 'DPD',
                            'status' => true,
                        ],
                        'conditions' => [
                            'has_pickup_only_today' => false,
                            'has_multipacks' => true,
                            'has_cod' => true,
                            'has_open_package' => true,
                            'has_rod' => true,
                            'has_rop' => false,
                            'has_saturday_delivery' => false,
                            'has_sms_notify' => true,
                            'has_swap' => false,
                        ],
                    ],
                    [
                        'id' => 2,
                        'slug' => 'fan_standard',
                        'name' => 'Fan Standard',
                        'status' => false,
                    ],
                ],
            ], JSON_THROW_ON_ERROR)),
        );
        $factory = new HttpFactory();

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: new ClientConfig(),
        );

        $services = $client->services()->getServices();
        $firstService = $services->first();

        $this->assertSame(2, $services->count());
        $this->assertInstanceOf(Service::class, $firstService);
        $this->assertSame('dpd_standard', $firstService->slug);
        $this->assertSame('DPD Standard', $firstService->fullName);
        $this->assertTrue($firstService->status);
        $this->assertTrue($firstService->active);
        $this->assertNotNull($firstService->courier);
        $this->assertSame('dpd', $firstService->courier?->slug);
        $this->assertNotNull($firstService->conditions);
        $this->assertTrue($firstService->conditions?->hasCod);
        $this->assertSame('/api/v1/services', $httpClient->lastRequest?->getUri()->getPath());
    }
}
