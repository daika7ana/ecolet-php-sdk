<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Support;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class HttpFoundationBridge
{
    public static function toSymfonyRequest(RequestInterface $request): SymfonyRequest
    {
        $uri = $request->getUri();
        $queryParams = [];
        parse_str($uri->getQuery(), $queryParams);

        $server = [
            'REQUEST_METHOD' => $request->getMethod(),
            'REQUEST_URI' => $uri->getPath() . ($uri->getQuery() !== '' ? '?' . $uri->getQuery() : ''),
            'QUERY_STRING' => $uri->getQuery(),
            'HTTPS' => $uri->getScheme() === 'https' ? 'on' : 'off',
            'HTTP_HOST' => $uri->getHost(),
            'SERVER_PORT' => (string) ($uri->getPort() ?? ($uri->getScheme() === 'https' ? 443 : 80)),
        ];

        foreach ($request->getHeaders() as $name => $values) {
            $headerName = (string) $name;
            $normalized = 'HTTP_' . strtoupper(str_replace('-', '_', $headerName));

            if (in_array(strtolower($headerName), ['content-type', 'content-length'], true)) {
                $normalized = strtoupper(str_replace('-', '_', $headerName));
            }

            $server[$normalized] = implode(', ', $values);
        }

        $body = (string) $request->getBody();

        return new SymfonyRequest(
            query: $queryParams,
            request: [],
            attributes: [],
            cookies: [],
            files: [],
            server: $server,
            content: $body,
        );
    }

    public static function toSymfonyResponse(ResponseInterface $response): SymfonyResponse
    {
        return new SymfonyResponse(
            content: (string) $response->getBody(),
            status: $response->getStatusCode(),
            headers: $response->getHeaders(),
        );
    }

    public static function fromSymfonyRequest(
        SymfonyRequest $request,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
    ): RequestInterface {
        $uri = $request->getSchemeAndHttpHost() . $request->getRequestUri();
        $psrRequest = $requestFactory->createRequest($request->getMethod(), $uri);

        foreach ($request->headers->all() as $name => $values) {
            $headerValues = array_map(
                static fn(?string $value): string => (string) $value,
                $values,
            );

            $psrRequest = $psrRequest->withHeader($name, $headerValues);
        }

        $content = $request->getContent();

        if ($content !== false && $content !== '') {
            $psrRequest = $psrRequest->withBody($streamFactory->createStream($content));
        }

        return $psrRequest;
    }
}
