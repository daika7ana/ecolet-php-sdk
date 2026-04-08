<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\Resources;

use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Config\ClientConfig;
use Daika7ana\Ecolet\Tests\Support\FakeHttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class UserResourceTest extends TestCase
{
    public function testGetMeMapsToUserDto(): void
    {
        $httpClient = new FakeHttpClient(
            static fn() => new Response(200, [], json_encode([
                'user' => [
                    'id' => 10,
                    'email' => 'john@example.com',
                    'name' => 'John Doe',
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

        $user = $client->users()->getMe();

        $this->assertSame(10, $user->id);
        $this->assertSame('john@example.com', $user->email);
        $this->assertSame('/api/v1/me', $httpClient->lastRequest?->getUri()->getPath());
    }
}
