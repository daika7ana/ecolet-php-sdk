<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Http;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpClientInterface extends ClientInterface
{
    public function sendRequest(RequestInterface $request): ResponseInterface;
}
