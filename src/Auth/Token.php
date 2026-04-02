<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Auth;

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

        return $this->expiresAt <= new DateTimeImmutable();
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
            $expiresAt = new DateTimeImmutable($data['expires_at']);
        }

        return new self(
            accessToken: $data['access_token'],
            tokenType: $data['token_type'] ?? 'Bearer',
            refreshToken: $data['refresh_token'] ?? null,
            expiresAt: $expiresAt,
        );
    }

    public static function fromResponse(array $data): self
    {
        $expiresAt = null;

        if (isset($data['expires_in'])) {
            $expiresAt = new DateTimeImmutable(sprintf('+%d seconds', $data['expires_in']));
        }

        return new self(
            accessToken: $data['access_token'],
            tokenType: $data['token_type'] ?? 'Bearer',
            refreshToken: $data['refresh_token'] ?? null,
            expiresAt: $expiresAt,
        );
    }
}
