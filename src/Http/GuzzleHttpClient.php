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
    public const DEFAULT_TIMEOUT = 30.0;

    public const DEFAULT_CONNECT_TIMEOUT = 10.0;

    private GuzzleClient $guzzleClient;

    public function __construct(
        ?GuzzleClient $guzzleClient = null,
        private ?float $timeout = null,
        private ?float $connectTimeout = null,
    ) {
        $this->guzzleClient = $guzzleClient ?? new GuzzleClient([
            'timeout' => $timeout ?? self::DEFAULT_TIMEOUT,
            'connect_timeout' => $connectTimeout ?? self::DEFAULT_CONNECT_TIMEOUT,
        ]);
    }

    /**
     * @throws TransportException
     */
    public function sendRequest(RequestInterface $request, ?float $timeout = null): ResponseInterface
    {
        $options = [
            'http_errors' => false,
            'allow_redirects' => false,
        ];

        $effectiveTimeout = $timeout ?? $this->timeout;
        if ($effectiveTimeout !== null) {
            $options['timeout'] = $effectiveTimeout;
        }

        if ($this->connectTimeout !== null) {
            $options['connect_timeout'] = $this->connectTimeout;
        }

        try {
            return $this->guzzleClient->send($request, $options);
        } catch (GuzzleException $e) {
            throw new TransportException(
                message: $e->getMessage(),
                code: $e->getCode(),
                previous: $e,
            );
        }
    }
}
