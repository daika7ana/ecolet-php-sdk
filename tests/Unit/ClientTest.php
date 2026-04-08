<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit;

use Daika7ana\Ecolet\Auth\Token;
use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Config\ClientConfig;
use Daika7ana\Ecolet\Tests\Support\FakeHttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testFromEnvironmentUsesConfiguredTestModeFlag(): void
    {
        ClientConfig::setTestMode(true);
        $stagingConfig = ClientConfig::fromEnvironment();

        ClientConfig::setTestMode(false);
        $productionConfig = ClientConfig::fromEnvironment();

        $this->assertSame(ClientConfig::BASE_URL_STAGING, $stagingConfig->baseUrl);
        $this->assertSame(ClientConfig::BASE_URL_PRODUCTION, $productionConfig->baseUrl);
    }

    public function testClientCanBeCreated(): void
    {
        $resolvedConfig = ClientConfig::fromEnvironment();
        $client = Client::create(config: $resolvedConfig);

        $this->assertNotNull($client);
        $config = $client->getConfig();
        $this->assertSame($resolvedConfig->baseUrl, $config->baseUrl);
    }

    public function testClientCanSetToken(): void
    {
        $client = Client::create();
        $client->setToken('test-token-123');

        $token = $client->getToken();
        $this->assertNotNull($token);
        $this->assertSame('test-token-123', $token->accessToken);

        $config = $client->getConfig();
        $this->assertNotNull($config->token);
        $this->assertSame('test-token-123', $config->token->accessToken);
    }

    public function testAuthenticatedRequestContainsBearerAndAcceptHeaders(): void
    {
        $httpClient = new FakeHttpClient(static fn() => new Response(200));
        $factory = new HttpFactory();

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: ClientConfig::fromEnvironment(),
        );

        $client->setToken('token-abc');

        $request = $client->createRequest('GET', '/v1/me');
        $client->send($request);

        $sentRequest = $httpClient->lastRequest;
        $this->assertNotNull($sentRequest);
        $this->assertSame('Bearer token-abc', $sentRequest->getHeaderLine('Authorization'));
        $this->assertSame('application/json', $sentRequest->getHeaderLine('Accept'));
    }

    public function testCustomPsr18ClientInjectionIsUsedForRequests(): void
    {
        $httpClient = new FakeHttpClient(static fn() => new Response(200, [], '{}'));
        $factory = new HttpFactory();

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: ClientConfig::fromEnvironment(),
        );

        $request = $client->createRequest('GET', '/v1/services');
        $response = $client->send($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('/api/v1/services', $httpClient->lastRequest?->getUri()->getPath());
    }

    public function testClientCanRestoreFullTokenObject(): void
    {
        $client = Client::create(config: ClientConfig::fromEnvironment());

        $client->setToken(new Token(
            accessToken: 'access-123',
            tokenType: 'Bearer',
            refreshToken: 'refresh-123',
        ));

        $config = $client->getConfig();
        $this->assertNotNull($config->token);
        $this->assertSame('access-123', $config->token->accessToken);
        $this->assertSame('refresh-123', $config->token->refreshToken);

        $token = $client->getToken();
        $this->assertNotNull($token);
        $this->assertSame('access-123', $token->accessToken);
        $this->assertSame('refresh-123', $token->refreshToken);
    }

    public function testRefreshTokenWorksForRestoredClientWhenOAuthCredentialsExistInConfig(): void
    {
        $httpClient = new FakeHttpClient(
            static fn() => new Response(200, [], json_encode([
                'token_type' => 'Bearer',
                'expires_in' => 3600,
                'access_token' => 'new-access-token',
                'refresh_token' => 'new-refresh-token',
            ], JSON_THROW_ON_ERROR)),
        );
        $factory = new HttpFactory();
        $config = ClientConfig::fromEnvironment()
            ->withOAuthCredentials('client-id', 'client-secret')
            ->withToken(new Token(
                accessToken: 'old-access-token',
                tokenType: 'Bearer',
                refreshToken: 'old-refresh-token',
            ));

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: $config,
        );

        $client->refreshToken();

        $token = $client->getConfig()->token;
        $this->assertNotNull($token);
        $this->assertSame('new-access-token', $token->accessToken);
        $this->assertSame('new-refresh-token', $token->refreshToken);

        parse_str((string) $httpClient->lastRequest?->getBody(), $payload);
        $this->assertSame('refresh_token', $payload['grant_type']);
        $this->assertSame('old-refresh-token', $payload['refresh_token']);
        $this->assertSame('client-id', $payload['client_id']);
        $this->assertSame('client-secret', $payload['client_secret']);
    }
}
