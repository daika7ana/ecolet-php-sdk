<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\Resources;

use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Config\ClientConfig;
use Daika7ana\Ecolet\DTOs\Common\Address;
use Daika7ana\Ecolet\Tests\Support\FakeHttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class AddressBookResourceTest extends TestCase
{
    private function makeClient(FakeHttpClient $httpClient): Client
    {
        $factory = new HttpFactory();

        return Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: new ClientConfig(),
        );
    }

    public function testGetAddressesReturnsAddressesAndPagination(): void
    {
        $httpClient = new FakeHttpClient(
            static fn() => new Response(200, [], json_encode([
                'data' => [
                    [
                        'id' => 7,
                        'name' => 'Warehouse',
                        'locality_id' => 323,
                        'country' => 'ro',
                        'county' => 'Bucuresti',
                        'locality' => 'Bucuresti',
                        'postal_code' => '011318',
                        'has_streets' => true,
                        'street_name' => 'Bucuresti-Ploiesti',
                        'street_number' => '172-176',
                        'block' => '1',
                        'entrance' => 'A2',
                        'floor' => '1',
                        'flat' => 'A3a',
                        'contact_person' => 'Test Test',
                        'email' => 'test@ecolet.ro',
                        'phone' => '0214824089',
                        'map_point_id' => null,
                        'map_point_name' => null,
                        'updated_at' => '2026-10-01T10:00:00+00:00',
                        'created_at' => '2026-09-01T10:00:00+00:00',
                    ],
                ],
                'links' => ['first' => 'https://panel.ecolet.ro/api/v1/address-book?page=1', 'next' => null],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 1],
            ], JSON_THROW_ON_ERROR)),
        );

        $result = $this->makeClient($httpClient)->addressBook()->getAddresses();

        $this->assertSame('/api/v1/address-book', $httpClient->lastRequest?->getUri()->getPath());
        $this->assertSame('', $httpClient->lastRequest?->getUri()->getQuery());
        $this->assertCount(1, $result->addresses);

        $address = $result->addresses->first();
        $this->assertInstanceOf(Address::class, $address);
        $this->assertSame(7, $address->id);
        $this->assertSame('Warehouse', $address->name);
        $this->assertSame('011318', $address->postalCode);
        $this->assertTrue($address->hasStreets);
        $this->assertSame('A3a', $address->flat);
        $this->assertNull($address->mapPointId);
        $this->assertSame(1, $result->meta['total']);
        $this->assertSame('https://panel.ecolet.ro/api/v1/address-book?page=1', $result->links['first']);
    }

    public function testGetAddressesPassesPaginationAndSearchQuery(): void
    {
        $httpClient = new FakeHttpClient(
            static fn() => new Response(200, [], json_encode(['data' => []], JSON_THROW_ON_ERROR)),
        );

        $this->makeClient($httpClient)->addressBook()->getAddresses(page: 2, searchQuery: 'Main St');

        $uri = $httpClient->lastRequest?->getUri();
        $this->assertSame('/api/v1/address-book', $uri?->getPath());
        $this->assertSame('page=2&searchQuery=Main%20St', $uri?->getQuery());
    }

    public function testGetAddressesToleratesMissingPayload(): void
    {
        $httpClient = new FakeHttpClient(
            static fn() => new Response(200, [], json_encode([], JSON_THROW_ON_ERROR)),
        );

        $result = $this->makeClient($httpClient)->addressBook()->getAddresses();

        $this->assertCount(0, $result->addresses);
        $this->assertSame([], $result->links);
        $this->assertSame([], $result->meta);
    }
}
