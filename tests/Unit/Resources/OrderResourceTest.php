<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\Resources;

use Daika7ana\Ecolet\Exceptions\UnexpectedStatusException;
use Daika7ana\Ecolet\Exceptions\ValidationException;
use Daika7ana\Ecolet\Tests\Support\ResponseFixtureFactory;
use Daika7ana\Ecolet\Tests\Support\TestClientFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class OrderResourceTest extends TestCase
{
    public function testGetOrderReturnsDto(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => new Response(200, [], json_encode([
                'data' => [
                    'id' => 123,
                    'awb' => '80438360579',
                    'status' => 'new',
                ],
            ], JSON_THROW_ON_ERROR)),
        );

        $order = $client->orders()->getOrder(123);

        $this->assertSame('80438360579', $order->number);
        $this->assertSame('new', $order->status);
        $this->assertSame(123, $order->id);
        $this->assertSame('/api/v1/order/123', $httpClient->lastRequest?->getUri()->getPath());
    }

    public function testDownloadWaybillReturnsWaybillDocument(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => new Response(200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename=waybill.pdf',
            ], '%PDF-binary%'),
        );

        $waybill = $client->orders()->downloadWaybill(123);

        $this->assertSame('application/pdf', $waybill->contentType);
        $this->assertStringContainsString('filename=waybill.pdf', (string) $waybill->contentDisposition);
        $this->assertSame('/api/v1/order/123/download-waybill', $httpClient->lastRequest?->getUri()->getPath());
    }

    public function testGetOrderThrowsValidationExceptionForInvalidId(): void
    {
        [$client, ] = TestClientFactory::create(
            static fn() => new Response(
                422,
                [],
                json_encode(
                    ResponseFixtureFactory::validationError([
                        'id' => ['The selected id is invalid.'],
                    ]),
                    JSON_THROW_ON_ERROR,
                ),
            ),
        );

        try {
            $client->orders()->getOrder(999);
            $this->fail('Expected ValidationException to be thrown.');
        } catch (ValidationException $exception) {
            $this->assertSame('The given data was invalid.', $exception->getMessage());
            $this->assertSame([
                'id' => ['The selected id is invalid.'],
            ], $exception->errors);
        }
    }

    public function testDownloadWaybillThrowsUnexpectedStatusExceptionForServerError(): void
    {
        [$client, ] = TestClientFactory::create(
            static fn() => new Response(
                500,
                [],
                json_encode(ResponseFixtureFactory::serverError(), JSON_THROW_ON_ERROR),
            ),
        );

        $this->expectException(UnexpectedStatusException::class);

        $client->orders()->downloadWaybill(123);
    }
}
