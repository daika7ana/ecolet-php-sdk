<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Http;

use Daika7ana\Ecolet\Exceptions\TransportException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Adapts any PSR-18 client to the SDK transport contract.
 *
 * The wrapped client keeps its own timeout; the SDK's per-call timeout cannot be forwarded and is rejected.
 */
final class Psr18HttpClientAdapter implements HttpClientInterface
{
    public function __construct(
        private ClientInterface $client,
    ) {}

    /**
     * @throws TransportException
     */
    public function sendRequest(RequestInterface $request, ?float $timeout = null): ResponseInterface
    {
        if ($timeout !== null) {
            throw new \InvalidArgumentException('A foreign PSR-18 client cannot honour the SDK per-call timeout; configure the timeout on that client.');
        }

        try {
            return $this->client->sendRequest($request);
        } catch (TransportException $e) {
            throw $e;
        } catch (ClientExceptionInterface $e) {
            throw new TransportException($e->getMessage(), (int) $e->getCode(), previous: $e);
        }
    }
}
