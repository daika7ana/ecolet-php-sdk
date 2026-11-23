<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\Exceptions;

use Daika7ana\Ecolet\Exceptions\UnexpectedStatusException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class UnexpectedStatusExceptionTest extends TestCase
{
    public function testAcceptsNullResponse(): void
    {
        $exception = new UnexpectedStatusException();

        $this->assertNull($exception->response);
        $this->assertSame('Unexpected HTTP status', $exception->getMessage());
        $this->assertNull($exception->retryAfter);
        $this->assertNull($exception->error);
    }

    public function testDerivesCodeFromResponseStatus(): void
    {
        $this->assertSame(503, (new UnexpectedStatusException(new Response(503)))->getCode());
        $this->assertSame(0, (new UnexpectedStatusException())->getCode());
        $this->assertSame(7, (new UnexpectedStatusException(null, code: 7))->getCode());
    }

    public function testDerivesMessageFromResponseStatus(): void
    {
        $exception = new UnexpectedStatusException(new Response(503));

        $this->assertSame(503, $exception->response?->getStatusCode());
        $this->assertSame('Unexpected HTTP status 503', $exception->getMessage());
    }
}
