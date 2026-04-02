<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\Http;

use Daika7ana\Ecolet\Config\ClientConfig;
use Daika7ana\Ecolet\Exceptions\TransportException;
use Daika7ana\Ecolet\Http\GuzzleHttpClient;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;

class GuzzleHttpClientTest extends TestCase
{
    public function testTransportErrorsAreMappedToTransportException(): void
    {
        $request = new Request('GET', ClientConfig::BASE_URL_STAGING . '/v1/me');

        $mock = new MockHandler([
            new ConnectException('Network down', $request),
        ]);

        $guzzle = new GuzzleClient([
            'handler' => HandlerStack::create($mock),
        ]);

        $client = new GuzzleHttpClient($guzzle);

        $this->expectException(TransportException::class);
        $client->sendRequest($request);
    }
}
