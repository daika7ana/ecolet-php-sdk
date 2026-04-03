<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Smoke\Concerns;

use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Config\ClientConfig;

trait InteractsWithAuthenticatedSmokeClient
{
    /**
     * @return array{username: string, password: string, clientId: string, clientSecret: string, testMode: string}
     */
    protected function smokeCredentials(string $context): array
    {
        $credentials = [
            'username' => getenv('ECOLET_TEST_USERNAME') ?: '',
            'password' => getenv('ECOLET_TEST_PASSWORD') ?: '',
            'clientId' => getenv('ECOLET_TEST_CLIENT_ID') ?: '',
            'clientSecret' => getenv('ECOLET_TEST_CLIENT_SECRET') ?: '',
            'testMode' => getenv('ECOLET_TEST_MODE') ?: 'true',
        ];

        if (
            $credentials['username'] === ''
            || $credentials['password'] === ''
            || $credentials['clientId'] === ''
            || $credentials['clientSecret'] === ''
        ) {
            $this->markTestSkipped(sprintf(
                'Set ECOLET_TEST_USERNAME, ECOLET_TEST_PASSWORD, ECOLET_TEST_CLIENT_ID, ECOLET_TEST_CLIENT_SECRET to run smoke %s test.',
                $context,
            ));
        }

        return $credentials;
    }

    protected function makeAuthenticatedClient(string $context, ?string $baseUrl = null): Client
    {
        $credentials = $this->smokeCredentials($context);
        $config = $baseUrl ? new ClientConfig(baseUrl: $baseUrl) : ClientConfig::fromEnvironment();
        $client = Client::create(config: $config);
        $client->authenticate(
            $credentials['username'],
            $credentials['password'],
            $credentials['clientId'],
            $credentials['clientSecret'],
        );

        return $client;
    }
}
