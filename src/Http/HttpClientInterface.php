<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Http;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpClientInterface extends ClientInterface
{
    /**
     * Send an HTTP request.
     *
     * @param RequestInterface $request The request to send
     * @param float|null $timeout Optional request timeout in seconds. If null, the HTTP client default is used.
     *
     * @throws \Psr\Http\Client\ClientExceptionInterface If an error happens while processing the request.
     */
    public function sendRequest(RequestInterface $request, ?float $timeout = null): ResponseInterface;
}
