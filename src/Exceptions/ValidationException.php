<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Exceptions;

use Exception;

class ValidationException extends EcoletException
{
    /**
     * @param array<string, string[]> $errors
     */
    public function __construct(
        public readonly array $errors,
        string $message = '',
        int $code = 0,
        ?Exception $previous = null,
    ) {
        parent::__construct($message ?: 'Validation failed', $code, $previous);
    }
}
