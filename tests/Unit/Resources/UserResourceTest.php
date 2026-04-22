<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\Resources;

use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Config\ClientConfig;
use Daika7ana\Ecolet\Tests\Support\FakeHttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class UserResourceTest extends TestCase
{
    public function testGetMeMapsToUserDto(): void
    {
        $httpClient = new FakeHttpClient(
            static fn() => new Response(200, [], json_encode([
                'user' => [
                    'id' => 10,
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'is_banned' => false,
                    'verified' => true,
                    'account_type' => 'company',
                    'tax_id' => 'RO12345678',
                    'phone' => '0712345678',
                    'country' => 'ro',
                    'balance' => 500.01,
                    'credit_usage' => 147.56,
                    'credit_limit' => 1000,
                    'printer_type' => 'pdf',
                    'printer_format' => 'A4',
                    'invoice_address_id' => 54,
                    'default_address_id' => 55,
                    'bank_account_id' => 63,
                    'agreement_marketing' => true,
                    'new_invoices_count' => 3,
                    'not_paid_invoices' => 1,
                    'new_contact_form_messages' => 2,
                    'new_orders_to_send_count' => 4,
                    'danger_banner' => 'Balance running low.',
                    'block_shipment' => false,
                    'forbidden_couriers' => ['4'],
                    'forbidden_services' => ['5'],
                ],
            ], JSON_THROW_ON_ERROR)),
        );
        $factory = new HttpFactory();

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: new ClientConfig(),
        );

        $user = $client->users()->getMe();

        $this->assertSame(10, $user->id);
        $this->assertSame('John Doe', $user->name);
        $this->assertSame('john@example.com', $user->email);
        $this->assertTrue($user->verified);
        $this->assertSame('company', $user->accountType);
        $this->assertSame('RO12345678', $user->taxId);
        $this->assertSame(500.01, $user->balance);
        $this->assertSame(147.56, $user->creditUsage);
        $this->assertSame(1000.0, $user->creditLimit);
        $this->assertSame('pdf', $user->printerType);
        $this->assertSame('A4', $user->printerFormat);
        $this->assertSame(54, $user->invoiceAddressId);
        $this->assertSame(['4'], $user->forbiddenCouriers);
        $this->assertSame(['5'], $user->forbiddenServices);
        $this->assertSame('/api/v1/me', $httpClient->lastRequest?->getUri()->getPath());
    }
}
