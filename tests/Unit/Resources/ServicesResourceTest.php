<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\Resources;

use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Config\ClientConfig;
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
                    ['id' => 1, 'name' => 'Express', 'active' => true],
                    ['id' => 2, 'name' => 'Standard', 'active' => false],
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

        $this->assertSame(2, $services->count());
        $this->assertSame('/api/v1/services', $httpClient->lastRequest?->getUri()->getPath());
    }
}
