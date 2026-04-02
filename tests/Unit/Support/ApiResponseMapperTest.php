<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\Support;

use Daika7ana\Ecolet\Exceptions\UnexpectedStatusException;
use Daika7ana\Ecolet\Exceptions\ValidationException;
use Daika7ana\Ecolet\Support\ApiResponseMapper;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ApiResponseMapperTest extends TestCase
{
    public function testDecodeJsonReturnsDecodedPayloadOnExpectedStatus(): void
    {
        $response = new Response(200, [], '{"ok":true}');

        $decoded = ApiResponseMapper::decodeJson($response);

        $this->assertTrue((bool) $decoded['ok']);
    }

    public function testDecodeJsonThrowsValidationExceptionFor422(): void
    {
        $response = new Response(422, [], '{"message":"Invalid input","errors":{"parcels":["Required"]}}');

        $this->expectException(ValidationException::class);
        ApiResponseMapper::decodeJson($response);
    }

    public function testDecodeJsonThrowsUnexpectedStatusForNonExpectedStatus(): void
    {
        $response = new Response(500, [], '{"message":"Server error"}');

        $this->expectException(UnexpectedStatusException::class);
        ApiResponseMapper::decodeJson($response);
    }
}
