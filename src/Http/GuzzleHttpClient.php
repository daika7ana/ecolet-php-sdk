<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Http;

use Daika7ana\Ecolet\Exceptions\TransportException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleHttpClient implements HttpClientInterface
{
    public function __construct(
        private GuzzleClient $guzzleClient = new GuzzleClient(),
    ) {}

    /**
     * @throws TransportException
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            return $this->guzzleClient->sendRequest($request);
        } catch (GuzzleException $e) {
            throw new TransportException(
                message: $e->getMessage(),
                code: $e->getCode(),
                previous: $e,
            );
        }
    }
}
