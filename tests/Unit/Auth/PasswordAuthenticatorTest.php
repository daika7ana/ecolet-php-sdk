<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\Auth;

use Daika7ana\Ecolet\Auth\PasswordAuthenticator;
use Daika7ana\Ecolet\Config\ClientConfig;
use Daika7ana\Ecolet\Exceptions\AuthenticationException;
use Daika7ana\Ecolet\Tests\Support\FakeHttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class PasswordAuthenticatorTest extends TestCase
{
    public function testPasswordGrantSuccess(): void
    {
        $httpClient = new FakeHttpClient(
            static fn() => new Response(200, [], json_encode([
                'token_type' => 'Bearer',
                'expires_in' => 3600,
                'access_token' => 'access-123',
                'refresh_token' => 'refresh-123',
            ], JSON_THROW_ON_ERROR)),
        );

        $factory = new HttpFactory();

        $authenticator = new PasswordAuthenticator(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            username: 'user@example.com',
            password: 'secret',
            clientId: 'client-id',
            clientSecret: 'client-secret',
            baseUrl: ClientConfig::BASE_URL_STAGING,
        );

        $token = $authenticator->authenticate();

        $this->assertSame('access-123', $token->accessToken);
        $this->assertSame('refresh-123', $token->refreshToken);

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('application/x-www-form-urlencoded', $request->getHeaderLine('Content-Type'));
        $this->assertSame('application/json', $request->getHeaderLine('Accept'));

        parse_str((string) $request->getBody(), $payload);
        $this->assertSame('password', $payload['grant_type']);
        $this->assertSame('user@example.com', $payload['username']);
        $this->assertSame('secret', $payload['password']);
        $this->assertSame('client-id', $payload['client_id']);
        $this->assertSame('client-secret', $payload['client_secret']);
    }

    public function testPasswordGrantFailureThrowsAuthenticationException(): void
    {
        $httpClient = new FakeHttpClient(
            static fn() => new Response(401, [], '{"error":"invalid_grant"}'),
        );
        $factory = new HttpFactory();

        $authenticator = new PasswordAuthenticator(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            username: 'user@example.com',
            password: 'wrong-secret',
            clientId: 'client-id',
            clientSecret: 'client-secret',
        );

        $this->expectException(AuthenticationException::class);
        $authenticator->authenticate();
    }

    public function testRefreshTokenSuccess(): void
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

        $authenticator = new PasswordAuthenticator(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            username: 'user@example.com',
            password: 'secret',
            clientId: 'client-id',
            clientSecret: 'client-secret',
        );

        $token = $authenticator->refresh('refresh-123');

        $this->assertSame('new-access-token', $token->accessToken);
        $this->assertSame('new-refresh-token', $token->refreshToken);

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        parse_str((string) $request->getBody(), $payload);
        $this->assertSame('refresh_token', $payload['grant_type']);
        $this->assertSame('refresh-123', $payload['refresh_token']);
    }

    public function testRefreshTokenFailureThrowsAuthenticationException(): void
    {
        $httpClient = new FakeHttpClient(
            static fn() => new Response(401, [], '{"error":"invalid_refresh_token"}'),
        );
        $factory = new HttpFactory();

        $authenticator = new PasswordAuthenticator(
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
            username: 'user@example.com',
            password: 'secret',
            clientId: 'client-id',
            clientSecret: 'client-secret',
        );

        $this->expectException(AuthenticationException::class);
        $authenticator->refresh('bad-refresh-token');
    }
}
