<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit;

use DateTimeImmutable;
use Daika7ana\Ecolet\Auth\InMemoryTokenStore;
use Daika7ana\Ecolet\Auth\Token;
use Daika7ana\Ecolet\Auth\TokenStoreInterface;
use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Config\ClientConfig;
use Daika7ana\Ecolet\Exceptions\AuthenticationException;
use Daika7ana\Ecolet\Exceptions\TransportException;
use Daika7ana\Ecolet\Tests\Support\FakeHttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    protected function tearDown(): void
    {
        ClientConfig::setTestMode(false);
    }

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
        $resolvedConfig = new ClientConfig();
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
            config: new ClientConfig(),
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
            config: new ClientConfig(),
        );

        $request = $client->createRequest('GET', '/v1/services');
        $response = $client->send($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('/api/v1/services', $httpClient->lastRequest?->getUri()->getPath());
    }

    public function testClientCanRestoreFullTokenObject(): void
    {
        $client = Client::create(config: new ClientConfig());

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
        $config = (new ClientConfig())
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

    public function testTokenStoreTokenOverridesConfigTokenForAuthorizedRequests(): void
    {
        $httpClient = new FakeHttpClient(static fn() => new Response(200, [], '{}'));
        $factory = new HttpFactory();
        $tokenStore = new InMemoryTokenStore();
        $tokenStore->setToken(new Token('store-token'));

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: (new ClientConfig())->withToken(new Token('config-token')),
            tokenStore: $tokenStore,
        );

        $request = $client->createRequest('GET', '/v1/me');

        $this->assertSame('Bearer store-token', $request->getHeaderLine('Authorization'));
        $this->assertSame('store-token', $client->getToken()?->accessToken);
        $this->assertSame('store-token', $client->getConfig()->token?->accessToken);
    }

    public function testGetTokenReturnsNullAfterTokenStoreIsCleared(): void
    {
        $tokenStore = new InMemoryTokenStore();
        $tokenStore->setToken(new Token('access-token'));

        $client = Client::create(
            config: (new ClientConfig())->withToken(new Token('config-token')),
            tokenStore: $tokenStore,
        );

        $this->assertSame('access-token', $client->getToken()?->accessToken);

        $tokenStore->clearToken();

        $this->assertNull($client->getToken());
        $this->assertNull($client->getConfig()->token);
    }

    public function testGetConfigReflectsExternalTokenStoreUpdateWithoutCallingGetTokenFirst(): void
    {
        $tokenStore = new InMemoryTokenStore();

        $client = Client::create(
            config: new ClientConfig(),
            tokenStore: $tokenStore,
        );

        $this->assertNull($client->getConfig()->token);

        $tokenStore->setToken(new Token('external-token'));

        $this->assertSame('external-token', $client->getConfig()->token?->accessToken);
    }

    public function testGetConfigReflectsExternalTokenStoreClearWithoutCallingGetTokenFirst(): void
    {
        $tokenStore = new InMemoryTokenStore();
        $tokenStore->setToken(new Token('access-token'));

        $client = Client::create(
            config: new ClientConfig(),
            tokenStore: $tokenStore,
        );

        $this->assertSame('access-token', $client->getConfig()->token?->accessToken);

        $tokenStore->clearToken();

        $this->assertNull($client->getConfig()->token);
    }

    public function testSendRefreshesExpiredTokenBeforeDispatchingAuthorizedRequest(): void
    {
        $requests = [];
        $httpClient = new FakeHttpClient(static function ($request) use (&$requests) {
            $requests[] = $request;

            if ($request->getUri()->getPath() === '/api/v1/oauth/token') {
                return new Response(200, [], json_encode([
                    'token_type' => 'Bearer',
                    'expires_in' => 3600,
                    'access_token' => 'fresh-access-token',
                    'refresh_token' => 'fresh-refresh-token',
                ], JSON_THROW_ON_ERROR));
            }

            return new Response(200, [], '{}');
        });
        $factory = new HttpFactory();

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: (new ClientConfig(clientId: 'client-id', clientSecret: 'client-secret'))->withToken(new Token(
                accessToken: 'expired-access-token',
                tokenType: 'Bearer',
                refreshToken: 'refresh-token',
                expiresAt: new DateTimeImmutable('-5 minutes'),
            )),
        );

        $response = $client->send($client->createRequest('GET', '/v1/me'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertCount(2, $requests);
        $this->assertSame('/api/v1/oauth/token', $requests[0]->getUri()->getPath());
        $this->assertSame('/api/v1/me', $requests[1]->getUri()->getPath());
        $this->assertSame('Bearer fresh-access-token', $requests[1]->getHeaderLine('Authorization'));
        $this->assertSame('fresh-access-token', $client->getToken()?->accessToken);
        $this->assertSame('fresh-access-token', $client->getConfig()->token?->accessToken);

        parse_str((string) $requests[0]->getBody(), $payload);
        $this->assertSame('refresh_token', $payload['grant_type']);
        $this->assertSame('refresh-token', $payload['refresh_token']);
    }

    public function testGlobalTimeoutIsForwardedToHttpClient(): void
    {
        $httpClient = new FakeHttpClient(static fn() => new Response(200, [], '{}'));
        $factory = new HttpFactory();

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: new ClientConfig(timeout: 15.0),
        );

        $request = $client->createRequest('GET', '/v1/me');
        $client->send($request);

        $this->assertSame(15.0, $httpClient->lastTimeout);
    }

    public function testPerRequestTimeoutOverridesGlobalTimeout(): void
    {
        $httpClient = new FakeHttpClient(static fn() => new Response(200, [], '{}'));
        $factory = new HttpFactory();

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: new ClientConfig(timeout: 15.0),
        );

        $request = $client->createRequest('GET', '/v1/me');
        $client->send($request, 5.0);

        $this->assertSame(5.0, $httpClient->lastTimeout);
    }

    public function testNoTimeoutByDefault(): void
    {
        $httpClient = new FakeHttpClient(static fn() => new Response(200, [], '{}'));
        $factory = new HttpFactory();

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: new ClientConfig(),
        );

        $request = $client->createRequest('GET', '/v1/me');
        $client->send($request);

        $this->assertNull($httpClient->lastTimeout);
    }

    public function testRefreshUsesTokenWrittenByAnotherProcessWhenRefreshFails(): void
    {
        $tokenStore = new InMemoryTokenStore();
        $expiredToken = new Token(
            accessToken: 'expired-access-token',
            refreshToken: 'old-refresh-token',
            expiresAt: new DateTimeImmutable('-5 minutes'),
        );
        $tokenStore->setToken($expiredToken);

        $httpClient = new FakeHttpClient(static function () use ($tokenStore) {
            // Simulate another worker refreshing the shared store while this request is in flight.
            $tokenStore->setToken(new Token(
                accessToken: 'fresh-access-token',
                refreshToken: 'fresh-refresh-token',
                expiresAt: new DateTimeImmutable('+1 hour'),
            ));

            return new Response(401, [], '{"error":"invalid_refresh_token"}');
        });
        $factory = new HttpFactory();

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: (new ClientConfig(clientId: 'client-id', clientSecret: 'client-secret'))->withToken($expiredToken),
            tokenStore: $tokenStore,
        );

        $client->refreshToken();

        $this->assertSame('fresh-access-token', $client->getToken()?->accessToken);
        $this->assertSame('fresh-access-token', $client->getConfig()->token?->accessToken);
    }

    public function testRefreshTransportFailureStillUsesTokenWrittenByAnotherProcess(): void
    {
        $tokenStore = new InMemoryTokenStore();
        $expiredToken = new Token(
            accessToken: 'expired-access-token',
            refreshToken: 'old-refresh-token',
            expiresAt: new DateTimeImmutable('-5 minutes'),
        );
        $tokenStore->setToken($expiredToken);

        $httpClient = new FakeHttpClient(static function () use ($tokenStore) {
            // Another worker finished the refresh while this one timed out.
            $tokenStore->setToken(new Token(
                accessToken: 'fresh-access-token',
                refreshToken: 'fresh-refresh-token',
                expiresAt: new DateTimeImmutable('+1 hour'),
            ));

            throw new TransportException('cURL error 28: Operation timed out');
        });
        $factory = new HttpFactory();

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: (new ClientConfig(clientId: 'client-id', clientSecret: 'client-secret'))->withToken($expiredToken),
            tokenStore: $tokenStore,
        );

        $client->refreshToken();

        $this->assertSame('fresh-access-token', $client->getToken()?->accessToken);
    }

    public function testExpiringTokenIsSentWhenRefreshIsNotPossible(): void
    {
        $token = new Token(
            accessToken: 'almost-expired-token',
            expiresAt: new DateTimeImmutable('+20 seconds'),
        );
        $tokenStore = new InMemoryTokenStore();
        $tokenStore->setToken($token);

        $httpClient = new FakeHttpClient(static fn() => new Response(200, [], '{}'));
        $factory = new HttpFactory();

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: new ClientConfig(),
            tokenStore: $tokenStore,
        );

        $response = $client->send($client->createRequest('GET', '/v1/me'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Bearer almost-expired-token', $httpClient->lastRequest?->getHeaderLine('Authorization'));
    }

    public function testRefreshFailureIsNotSwallowedWhenStoreReturnsEqualTokenInstance(): void
    {
        $token = new Token(
            accessToken: 'still-valid-access-token',
            refreshToken: 'revoked-refresh-token',
            expiresAt: new DateTimeImmutable('+1 hour'),
        );

        // Stores outside the process return a fresh Token instance for the same token.
        $tokenStore = new class ($token) implements TokenStoreInterface {
            public function __construct(private ?Token $token) {}

            public function getToken(): ?Token
            {
                return $this->token === null ? null : clone $this->token;
            }

            public function setToken(Token $token): void
            {
                $this->token = $token;
            }

            public function clearToken(): void
            {
                $this->token = null;
            }
        };

        $httpClient = new FakeHttpClient(static fn() => new Response(400, [], '{"error":"invalid_grant"}'));
        $factory = new HttpFactory();

        $client = Client::create(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            config: (new ClientConfig(clientId: 'client-id', clientSecret: 'client-secret'))->withToken($token),
            tokenStore: $tokenStore,
        );

        $this->expectException(AuthenticationException::class);
        $client->refreshToken();
    }
}
