<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Exceptions;

use Throwable;

class TransportException extends EcoletException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message ?: 'Transport error', $code, $previous);
    }
}
