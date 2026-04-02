<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\Support;

use Daika7ana\Ecolet\Support\HttpFoundationBridge;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class HttpFoundationBridgeTest extends TestCase
{
    public function testConvertsPsrRequestToSymfonyRequest(): void
    {
        $request = new Request(
            method: 'POST',
            uri: 'https://example.test/api/v1/orders?status=new',
            headers: [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            body: '{"foo":"bar"}',
        );

        $symfonyRequest = HttpFoundationBridge::toSymfonyRequest($request);

        $this->assertSame('POST', $symfonyRequest->getMethod());
        $this->assertSame('new', $symfonyRequest->query->get('status'));
        $this->assertSame('application/json', $symfonyRequest->headers->get('accept'));
        $this->assertSame('{"foo":"bar"}', $symfonyRequest->getContent());
    }

    public function testConvertsPsrResponseToSymfonyResponse(): void
    {
        $response = new Response(
            status: 201,
            headers: ['Content-Type' => 'application/json'],
            body: '{"ok":true}',
        );

        $symfonyResponse = HttpFoundationBridge::toSymfonyResponse($response);

        $this->assertSame(201, $symfonyResponse->getStatusCode());
        $this->assertSame('application/json', $symfonyResponse->headers->get('content-type'));
        $this->assertSame('{"ok":true}', $symfonyResponse->getContent());
    }

    public function testConvertsSymfonyRequestToPsrRequest(): void
    {
        $symfonyRequest = SymfonyRequest::create(
            uri: 'https://example.test/api/v1/map-points/RO?foo=bar',
            method: 'POST',
            parameters: [],
            cookies: [],
            files: [],
            server: ['HTTP_ACCEPT' => 'application/json'],
            content: '{"service_id":1}',
        );

        $factory = new HttpFactory();
        $psrRequest = HttpFoundationBridge::fromSymfonyRequest($symfonyRequest, $factory, $factory);

        $this->assertSame('POST', $psrRequest->getMethod());
        $this->assertSame('/api/v1/map-points/RO', $psrRequest->getUri()->getPath());
        $this->assertSame('foo=bar', $psrRequest->getUri()->getQuery());
        $this->assertSame('application/json', $psrRequest->getHeaderLine('Accept'));
        $this->assertSame('{"service_id":1}', (string) $psrRequest->getBody());
    }
}
