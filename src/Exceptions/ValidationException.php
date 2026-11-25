<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Exceptions;

use Daika7ana\Ecolet\DTOs\Common\Errors\EcoletError;
use Throwable;
use Psr\Http\Message\ResponseInterface;

class ValidationException extends EcoletException
{
    /**
     * @param array<string, string[]> $errors
     */
    public function __construct(
        public readonly array $errors = [],
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        public readonly ?ResponseInterface $response = null,
        public readonly ?int $retryAfter = null,
        public readonly ?EcoletError $error = null,
    ) {
        parent::__construct($message ?: 'Validation failed', $response?->getStatusCode() ?? $code, $previous);
    }
}
