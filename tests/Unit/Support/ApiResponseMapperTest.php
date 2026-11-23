<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\Support;

use Daika7ana\Ecolet\Exceptions\AuthenticationException;
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

    public function testDecodeJsonUsesGeneralErrorAsValidationExceptionMessage(): void
    {
        $response = new Response(422, [], '{"general_error":"Service unavailable."}');

        try {
            ApiResponseMapper::decodeJson($response);
            $this->fail('Expected ValidationException to be thrown.');
        } catch (ValidationException $exception) {
            $this->assertSame('Service unavailable.', $exception->getMessage());
            $this->assertSame([], $exception->errors);
        }
    }

    public function testDecodeJsonFallsBackToDefaultValidationMessageForNonJsonBody(): void
    {
        $response = new Response(422, [], '<html>bad gateway</html>');

        try {
            ApiResponseMapper::decodeJson($response);
            $this->fail('Expected ValidationException to be thrown.');
        } catch (ValidationException $exception) {
            $this->assertSame('Validation failed', $exception->getMessage());
            $this->assertSame([], $exception->errors);
        }
    }

    public function testDecodeJsonThrowsUnexpectedStatusForNonExpectedStatus(): void
    {
        $response = new Response(500, [], '{"message":"Server error"}');

        $this->expectException(UnexpectedStatusException::class);
        ApiResponseMapper::decodeJson($response);
    }

    public function testDecodeJsonWrapsInvalidJsonInUnexpectedStatusException(): void
    {
        $response = new Response(200, [], '<html>not json</html>');

        $this->expectException(UnexpectedStatusException::class);
        ApiResponseMapper::decodeJson($response);
    }

    public function testDecodeJsonWrapsNonArrayJsonInUnexpectedStatusException(): void
    {
        $response = new Response(200, [], 'null');

        $this->expectException(UnexpectedStatusException::class);
        ApiResponseMapper::decodeJson($response);
    }

    public function testValidationExceptionExposesTheResponse(): void
    {
        $response = new Response(422, [], '{"message":"Invalid input","errors":{"parcels":["Required"]}}');

        try {
            ApiResponseMapper::decodeJson($response);
            $this->fail('Expected ValidationException to be thrown.');
        } catch (ValidationException $exception) {
            $this->assertSame($response, $exception->response);
            $this->assertSame(422, $exception->response?->getStatusCode());
        }
    }

    public function testValidationExceptionCarriesEcoletError(): void
    {
        $response = new Response(422, [], '{"message":"Invalid input","errors":{"parcels":["Required"]}}');

        try {
            ApiResponseMapper::decodeJson($response);
            $this->fail('Expected ValidationException to be thrown.');
        } catch (ValidationException $exception) {
            $this->assertNotNull($exception->error);
            $this->assertSame(422, $exception->error->code);
            $this->assertSame('Invalid input', $exception->error->message);
            $this->assertTrue($exception->error->isValidationRelated());
            $this->assertSame(['parcels' => ['Required']], $exception->error->raw['errors']);
        }
    }

    public function testDecodeJsonThrowsAuthenticationExceptionFor401(): void
    {
        $response = new Response(401, [], '{"error":"invalid_grant"}');

        try {
            ApiResponseMapper::decodeJson($response);
            $this->fail('Expected AuthenticationException to be thrown.');
        } catch (AuthenticationException $exception) {
            $this->assertSame(401, $exception->getCode());
            $this->assertSame($response, $exception->response);
            $this->assertSame('invalid_grant', $exception->getMessage());
            $this->assertNotNull($exception->error);
            $this->assertTrue($exception->error->isAuthenticationRelated());
        }
    }

    public function testDecodeJsonThrowsValidationExceptionFor400(): void
    {
        $response = new Response(400, [], '{"error":"bad request"}');

        try {
            ApiResponseMapper::decodeJson($response);
            $this->fail('Expected ValidationException to be thrown.');
        } catch (ValidationException $exception) {
            $this->assertSame(400, $exception->getCode());
            $this->assertSame($response, $exception->response);
            $this->assertNotNull($exception->error);
            $this->assertSame(400, $exception->error->code);
        }
    }

    public function testDecodeJsonThrowsUnexpectedStatusFor403WithAuthenticationFlag(): void
    {
        $response = new Response(403, [], '{"message":"Access rights missing"}');

        try {
            ApiResponseMapper::decodeJson($response);
            $this->fail('Expected UnexpectedStatusException to be thrown.');
        } catch (UnexpectedStatusException $exception) {
            $this->assertSame(403, $exception->getCode());
            $this->assertNotNull($exception->error);
            $this->assertTrue($exception->error->isAuthenticationRelated());
        }
    }

    public function testHttpFailuresCarryTheHttpStatusAsCode(): void
    {
        try {
            ApiResponseMapper::decodeJson(new Response(503, [], 'down'));
            $this->fail('Expected UnexpectedStatusException to be thrown.');
        } catch (UnexpectedStatusException $exception) {
            $this->assertSame(503, $exception->getCode());
        }

        try {
            ApiResponseMapper::decodeJson(new Response(200, [], '<html>not json</html>'));
            $this->fail('Expected UnexpectedStatusException to be thrown.');
        } catch (UnexpectedStatusException $exception) {
            $this->assertSame(200, $exception->getCode());
        }
    }

    public function testUnexpectedStatusExceptionCarriesEcoletError(): void
    {
        $response = new Response(503, [], '{"general_error":"Service unavailable."}');

        try {
            ApiResponseMapper::decodeJson($response);
            $this->fail('Expected UnexpectedStatusException to be thrown.');
        } catch (UnexpectedStatusException $exception) {
            $this->assertNotNull($exception->error);
            $this->assertSame(503, $exception->error->code);
            $this->assertSame('Service unavailable.', $exception->error->message);
        }
    }

    public function testInvalidJsonUnexpectedStatusCarriesEcoletErrorWithoutPayload(): void
    {
        $response = new Response(200, [], '<html>not json</html>');

        try {
            ApiResponseMapper::decodeJson($response);
            $this->fail('Expected UnexpectedStatusException to be thrown.');
        } catch (UnexpectedStatusException $exception) {
            $this->assertNotNull($exception->error);
            $this->assertSame(200, $exception->error->code);
            $this->assertSame([], $exception->error->raw);
        }
    }

    public function testPayloadHelpersNormalizeUnexpectedShapes(): void
    {
        $this->assertSame([], ApiResponseMapper::arrayOrEmpty('invalid'));
        $this->assertSame([
            ['id' => 1],
            ['id' => 2],
        ], ApiResponseMapper::listOfArrays(['invalid', ['id' => 1], 12, ['id' => 2]]));
        $this->assertSame(['one', '2', '1'], ApiResponseMapper::listOfStrings(['one', 2, ['skip'], true]));
    }

    public function testUnexpectedStatusExposesRetryAfterHeader(): void
    {
        try {
            ApiResponseMapper::decodeJson(new Response(503, ['Retry-After' => '30'], 'down'));
            $this->fail('Expected UnexpectedStatusException to be thrown.');
        } catch (UnexpectedStatusException $exception) {
            $this->assertSame(30, $exception->retryAfter);
        }

        try {
            ApiResponseMapper::decodeJson(new Response(503, [], 'down'));
            $this->fail('Expected UnexpectedStatusException to be thrown.');
        } catch (UnexpectedStatusException $exception) {
            $this->assertNull($exception->retryAfter);
        }
    }
}
