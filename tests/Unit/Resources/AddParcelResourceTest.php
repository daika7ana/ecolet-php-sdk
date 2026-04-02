<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\Resources;

use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Config\ClientConfig;
use Daika7ana\Ecolet\DTOs\AddParcelRequest;
use Daika7ana\Ecolet\Exceptions\ValidationException;
use Daika7ana\Ecolet\Tests\Support\FakeHttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class AddParcelResourceTest extends TestCase
{
    public function testSendOrderSupportsSingleParcelPayload(): void
    {
        $httpClient = new FakeHttpClient(
            static fn() => new Response(200, [], json_encode(['success' => true], JSON_THROW_ON_ERROR)),
        );
        $factory = new HttpFactory();

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: ClientConfig::fromEnvironment(),
        );

        $payload = AddParcelRequest::singleParcel(['weight' => 1.5, 'package_type' => 'box']);
        $result = $client->addParcel()->sendOrder($payload);

        $this->assertTrue((bool) $result->data['success']);
        $this->assertSame('/api/v2/add-parcel/send-order', $httpClient->lastRequest?->getUri()->getPath());

        $requestBody = (string) $httpClient->lastRequest?->getBody();
        $decoded = json_decode($requestBody, true, flags: JSON_THROW_ON_ERROR);
        $this->assertCount(1, $decoded['parcels']);
    }

    public function testSaveOrderSupportsMultipackPayload(): void
    {
        $httpClient = new FakeHttpClient(
            static fn() => new Response(200, [], json_encode(['queued' => true], JSON_THROW_ON_ERROR)),
        );
        $factory = new HttpFactory();

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: ClientConfig::fromEnvironment(),
        );

        $payload = AddParcelRequest::multipack(
            ['recipient' => ['name' => 'John Doe']],
            [
                ['weight' => 1.0],
                ['weight' => 2.0],
            ],
        );

        $result = $client->addParcel()->saveOrderToSend($payload);

        $this->assertTrue((bool) $result->data['queued']);
        $this->assertSame('/api/v2/add-parcel/save-order-to-send', $httpClient->lastRequest?->getUri()->getPath());

        $requestBody = (string) $httpClient->lastRequest?->getBody();
        $decoded = json_decode($requestBody, true, flags: JSON_THROW_ON_ERROR);
        $this->assertCount(2, $decoded['parcels']);
    }

    public function testAllOperationsReturnTypedResult(): void
    {
        $httpClient = new FakeHttpClient(
            static fn() => new Response(200, [], json_encode(['ok' => true], JSON_THROW_ON_ERROR)),
        );
        $factory = new HttpFactory();

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: ClientConfig::fromEnvironment(),
        );

        $payload = ['parcels' => [['weight' => 1.1]]];

        $reload = $client->addParcel()->reloadForm($payload);
        $send = $client->addParcel()->sendOrder($payload);
        $save = $client->addParcel()->saveOrderToSend($payload);

        $this->assertTrue((bool) $reload->data['ok']);
        $this->assertTrue((bool) $send->data['ok']);
        $this->assertTrue((bool) $save->data['ok']);
    }

    public function testSendOrderMaps422ToValidationException(): void
    {
        $httpClient = new FakeHttpClient(
            static fn() => new Response(422, [], json_encode([
                'message' => 'Validation failed',
                'errors' => [
                    'parcels' => ['At least one parcel is required.'],
                ],
            ], JSON_THROW_ON_ERROR)),
        );
        $factory = new HttpFactory();

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: ClientConfig::fromEnvironment(),
        );

        $this->expectException(ValidationException::class);
        $client->addParcel()->sendOrder(['parcels' => []]);
    }
}
