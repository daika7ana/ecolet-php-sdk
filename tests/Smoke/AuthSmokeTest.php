<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Smoke;

use Daika7ana\Ecolet\Tests\Smoke\Concerns\InteractsWithAuthenticatedSmokeClient;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

final class AuthSmokeTest extends TestCase
{
    use InteractsWithAuthenticatedSmokeClient;

    #[Group('smoke')]
    public function testPasswordGrantAuthenticationAgainstLiveApi(): void
    {
        $client = $this->makeAuthenticatedClient('auth');

        $token = $client->getConfig()->token;

        $this->assertNotNull($token);
        $this->assertNotSame('', $token->accessToken);
        $this->assertSame('Bearer', $token->tokenType);

        $request = $client->createRequest('GET', '/v1/me')
            ->withHeader('Accept', 'application/json');
        $response = $client->send($request);

        $this->assertSame(200, $response->getStatusCode());
    }

    #[Group('smoke')]
    public function testRefreshTokenAgainstLiveApi(): void
    {
        $client = $this->makeAuthenticatedClient('refresh token');

        $initialToken = $client->getConfig()->token;

        $this->assertNotNull($initialToken);
        $this->assertNotSame('', $initialToken->refreshToken ?? '');

        $client->refreshToken();

        $refreshedToken = $client->getConfig()->token;

        $this->assertNotNull($refreshedToken);
        $this->assertNotSame('', $refreshedToken->accessToken);
        $this->assertNotSame('', $refreshedToken->refreshToken ?? '');
        $this->assertSame('Bearer', $refreshedToken->tokenType);

        $request = $client->createRequest('GET', '/v1/me')
            ->withHeader('Accept', 'application/json');
        $response = $client->send($request);

        $this->assertSame(200, $response->getStatusCode());
    }
}
