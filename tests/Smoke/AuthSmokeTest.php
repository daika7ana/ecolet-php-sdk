<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Smoke;

use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Config\ClientConfig;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

final class AuthSmokeTest extends TestCase
{
    #[Group('smoke')]
    public function testPasswordGrantAuthenticationAgainstLiveApi(): void
    {
        $username = getenv('ECOLET_TEST_USERNAME') ?: '';
        $password = getenv('ECOLET_TEST_PASSWORD') ?: '';
        $clientId = getenv('ECOLET_TEST_CLIENT_ID') ?: '';
        $clientSecret = getenv('ECOLET_TEST_CLIENT_SECRET') ?: '';
        $testMode = getenv('ECOLET_TEST_MODE') ?: 'false';

        if ($username === '' || $password === '' || $clientId === '' || $clientSecret === '') {
            $this->markTestSkipped('Set ECOLET_TEST_USERNAME, ECOLET_TEST_PASSWORD, ECOLET_TEST_CLIENT_ID, ECOLET_TEST_CLIENT_SECRET to run smoke auth test.');
        }

        $config = new ClientConfig(baseUrl: ClientConfig::baseUrlFromTestMode($testMode));
        $client = Client::create(config: $config);
        $client->authenticate($username, $password, $clientId, $clientSecret);

        $token = $client->getConfig()->token;

        $this->assertNotNull($token);
        $this->assertNotSame('', $token->accessToken);
        $this->assertSame('Bearer', $token->tokenType);

        $request = $client->createRequest('GET', '/v1/me')
            ->withHeader('Accept', 'application/json');
        $response = $client->send($request);

        $this->assertSame(200, $response->getStatusCode());
    }
}
