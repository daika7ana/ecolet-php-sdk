<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\Auth;

use Daika7ana\Ecolet\Auth\Token;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{
    public function testTokenCreationFromResponse(): void
    {
        $data = [
            'access_token' => 'test-token-123',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ];

        $token = Token::fromResponse($data);

        $this->assertSame('test-token-123', $token->accessToken);
        $this->assertSame('Bearer', $token->tokenType);
        $this->assertFalse($token->isExpired());
    }

    public function testTokenIsNotExpiredWhenNoExpirySet(): void
    {
        $token = new Token(accessToken: 'test-token', tokenType: 'Bearer');

        $this->assertFalse($token->isExpired());
    }

    public function testTokenIsExpiredWhenExpiryInPast(): void
    {
        $expiresAt = new DateTimeImmutable('-1 hour');
        $token = new Token(
            accessToken: 'test-token',
            tokenType: 'Bearer',
            expiresAt: $expiresAt,
        );

        $this->assertTrue($token->isExpired());
    }

    public function testTokenCanRoundTripToAndFromArray(): void
    {
        $expiresAt = new DateTimeImmutable('2026-04-02T12:00:00+00:00');
        $token = new Token(
            accessToken: 'access-123',
            tokenType: 'Bearer',
            refreshToken: 'refresh-123',
            expiresAt: $expiresAt,
        );

        $serialized = $token->toArray();
        $restored = Token::fromArray($serialized);

        $this->assertSame('access-123', $restored->accessToken);
        $this->assertSame('Bearer', $restored->tokenType);
        $this->assertSame('refresh-123', $restored->refreshToken);
        $this->assertNotNull($restored->expiresAt);
        $this->assertSame($expiresAt->format(DATE_ATOM), $restored->expiresAt?->format(DATE_ATOM));
    }
}
