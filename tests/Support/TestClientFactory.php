<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Support;

use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Config\ClientConfig;
use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class TestClientFactory
{
    /**
     * @param callable(RequestInterface): ResponseInterface $handler
     * @return array{Client, FakeHttpClient}
     */
    public static function create(callable $handler): array
    {
        $httpClient = new FakeHttpClient($handler);
        $factory = new HttpFactory();

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: new ClientConfig(),
        );

        return [$client, $httpClient];
    }
}
