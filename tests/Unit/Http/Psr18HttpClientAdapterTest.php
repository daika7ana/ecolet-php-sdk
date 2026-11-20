<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\Http;

use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Config\ClientConfig;
use Daika7ana\Ecolet\Exceptions\TransportException;
use Daika7ana\Ecolet\Http\Psr18HttpClientAdapter;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class Psr18HttpClientAdapterTest extends TestCase
{
    public function testDelegatesToAnyPsr18ClientWithoutTimeout(): void
    {
        $inner = new class implements ClientInterface {
            public ?RequestInterface $lastRequest = null;

            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                $this->lastRequest = $request;

                return new Response(200, [], 'ok');
            }
        };

        $request = new Request('GET', 'https://example.test/ping');

        $response = (new Psr18HttpClientAdapter($inner))->sendRequest($request);

        $this->assertSame($request, $inner->lastRequest);
        $this->assertSame('ok', (string) $response->getBody());
    }

    public function testRejectsNonNilTimeoutWithoutCallingWrappedClient(): void
    {
        $inner = new class implements ClientInterface {
            public bool $called = false;

            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                $this->called = true;

                return new Response(200);
            }
        };

        $request = new Request('GET', 'https://example.test/ping');

        try {
            (new Psr18HttpClientAdapter($inner))->sendRequest($request, 30.0);
            $this->fail('Expected InvalidArgumentException.');
        } catch (\InvalidArgumentException $e) {
            $this->assertSame(
                'A foreign PSR-18 client cannot honour the SDK per-call timeout; configure the timeout on that client.',
                $e->getMessage(),
            );
        }

        $this->assertFalse($inner->called);
    }

    public function testWrapsPsr18ClientExceptionInTransportException(): void
    {
        $request = new Request('GET', 'https://example.test/ping');
        $original = new ConnectException('Connection refused', $request);

        $inner = new class ($original) implements ClientInterface {
            public function __construct(private ConnectException $exception) {}

            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                throw $this->exception;
            }
        };

        try {
            (new Psr18HttpClientAdapter($inner))->sendRequest($request);
            $this->fail('Expected TransportException.');
        } catch (TransportException $e) {
            $this->assertSame($original, $e->getPrevious());
        }
    }

    public function testClientAcceptsBarePsr18Client(): void
    {
        $bare = new class implements ClientInterface {
            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                return new Response(200);
            }
        };

        $factory = new HttpFactory();

        $client = Client::create(
            httpClient: $bare,
            requestFactory: $factory,
            streamFactory: $factory,
            config: new ClientConfig(),
        );

        $this->assertInstanceOf(Client::class, $client);
    }
}
