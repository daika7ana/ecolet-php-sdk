<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Exceptions;

use Daika7ana\Ecolet\DTOs\Common\Errors\EcoletError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class AuthenticationException extends EcoletException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        public readonly ?ResponseInterface $response = null,
        public readonly ?int $retryAfter = null,
        public readonly ?EcoletError $error = null,
    ) {
        parent::__construct($message, $response?->getStatusCode() ?? $code, $previous);
    }
}
