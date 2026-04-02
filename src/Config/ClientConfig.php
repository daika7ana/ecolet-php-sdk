<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Config;

use Daika7ana\Ecolet\Auth\Token;

final class ClientConfig
{
    public const BASE_URL_PRODUCTION = 'https://panel.ecolet.ro/api';

    public const BASE_URL_STAGING = 'https://staging.ecolet.ro/api';

    public function __construct(
        public string $baseUrl = self::BASE_URL_PRODUCTION,
        public ?Token $token = null,
        public string $clientId = '',
        public string $clientSecret = '',
        public string $scope = '',
        public string $acceptLanguage = 'en',
    ) {}

    public function withToken(Token $token): self
    {
        $config = clone $this;
        $config->token = $token;

        return $config;
    }

    public function withBaseUrl(string $baseUrl): self
    {
        $config = clone $this;
        $config->baseUrl = rtrim($baseUrl, '/');

        return $config;
    }

    public function withOAuthCredentials(string $clientId, string $clientSecret, string $scope = ''): self
    {
        $config = clone $this;
        $config->clientId = $clientId;
        $config->clientSecret = $clientSecret;
        $config->scope = $scope;

        return $config;
    }

    public static function fromEnvironment(): self
    {
        $mode = getenv('ECOLET_TEST_MODE');

        return new self(baseUrl: self::baseUrlFromTestMode($mode === false ? '' : $mode));
    }

    public static function baseUrlFromTestMode(string $mode): string
    {
        $isTestMode = filter_var($mode, FILTER_VALIDATE_BOOLEAN);

        return $isTestMode ? self::BASE_URL_STAGING : self::BASE_URL_PRODUCTION;
    }
}
