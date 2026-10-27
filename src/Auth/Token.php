<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Auth;

use Daika7ana\Ecolet\Exceptions\AuthenticationException;
use DateTimeImmutable;

final readonly class Token
{
    public function __construct(
        public string $accessToken,
        public string $tokenType = 'Bearer',
        public ?string $refreshToken = null,
        public ?DateTimeImmutable $expiresAt = null,
    ) {}

    public function isExpired(): bool
    {
        if ($this->expiresAt === null) {
            return false;
        }

        // Treat a token that expires within 30s as expired, so it is not sent mid-flight.
        return $this->expiresAt <= new DateTimeImmutable('+30 seconds');
    }

    /**
     * @return array{
     *     access_token: string,
     *     token_type: string,
     *     refresh_token: ?string,
     *     expires_at: ?string
     * }
     */
    public function toArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'token_type' => $this->tokenType,
            'refresh_token' => $this->refreshToken,
            'expires_at' => $this->expiresAt?->format(DATE_ATOM),
        ];
    }

    /**
     * @param array{
     *     access_token: string,
     *     token_type?: string,
     *     refresh_token?: ?string,
     *     expires_at?: ?string
     * } $data
     */
    public static function fromArray(array $data): self
    {
        $expiresAt = null;

        if (isset($data['expires_at']) && is_string($data['expires_at']) && $data['expires_at'] !== '') {
            try {
                $expiresAt = new DateTimeImmutable($data['expires_at']);
            } catch (\Exception) {
                // Corrupted cache data: treat as already expired so the token gets refreshed.
                $expiresAt = new DateTimeImmutable();
            }
        }

        $accessToken = (string) ($data['access_token'] ?? '');

        if ($accessToken === '') {
            throw new AuthenticationException('Cannot restore token: access_token is missing or empty.');
        }

        return new self(
            accessToken: $accessToken,
            tokenType: (string) ($data['token_type'] ?? 'Bearer'),
            refreshToken: isset($data['refresh_token']) ? (string) $data['refresh_token'] : null,
            expiresAt: $expiresAt,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromResponse(array $data): self
    {
        $expiresAt = null;

        if (isset($data['expires_in'])) {
            $expiresAt = new DateTimeImmutable(sprintf('+%d seconds', (int) $data['expires_in']));
        }

        return new self(
            accessToken: (string) ($data['access_token'] ?? ''),
            tokenType: (string) ($data['token_type'] ?? 'Bearer'),
            refreshToken: isset($data['refresh_token']) ? (string) $data['refresh_token'] : null,
            expiresAt: $expiresAt,
        );
    }
}
