<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\Exceptions;

use Daika7ana\Ecolet\Exceptions\ValidationException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ValidationExceptionTest extends TestCase
{
    public function testDerivesCodeFromResponseStatus(): void
    {
        $this->assertSame(422, (new ValidationException([], response: new Response(422)))->getCode());
        $this->assertSame(0, (new ValidationException([]))->getCode());
        $this->assertSame(5, (new ValidationException([], code: 5))->getCode());
    }
}
