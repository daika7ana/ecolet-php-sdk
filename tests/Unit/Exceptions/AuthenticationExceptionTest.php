<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\Exceptions;

use Daika7ana\Ecolet\Exceptions\AuthenticationException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class AuthenticationExceptionTest extends TestCase
{
    public function testDerivesCodeFromResponseStatus(): void
    {
        $this->assertSame(401, (new AuthenticationException(response: new Response(401)))->getCode());
        $this->assertSame(0, (new AuthenticationException())->getCode());
        $this->assertSame(5, (new AuthenticationException(code: 5))->getCode());
    }
}
