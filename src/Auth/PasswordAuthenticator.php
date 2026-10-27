<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Auth;

use Daika7ana\Ecolet\Config\ClientConfig;
use Daika7ana\Ecolet\Exceptions\AuthenticationException;
use Daika7ana\Ecolet\Exceptions\TransportException;
use Daika7ana\Ecolet\Http\HttpClientInterface;
use Daika7ana\Ecolet\Support\JsonHelper;
use JsonException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class PasswordAuthenticator
{
    private ClientConfig $config;

    public function __construct(
        private HttpClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
        private string $username,
        private string $password,
        ?ClientConfig $config = null,
    ) {
        $this->config = $config ?? ClientConfig::fromEnvironment();
    }

    /**
     * @throws AuthenticationException
     * @throws TransportException
     */
    public function authenticate(): Token
    {
        return $this->requestToken([
            'grant_type' => 'password',
            'username' => $this->username,
            'password' => $this->password,
            'client_id' => $this->config->clientId,
            'client_secret' => $this->config->clientSecret,
            'scope' => $this->config->scope,
        ], 'Authentication');
    }

    /**
     * @throws AuthenticationException
     * @throws TransportException
     */
    public function refresh(string $refreshToken): Token
    {
        return $this->requestToken([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $this->config->clientId,
            'client_secret' => $this->config->clientSecret,
            'scope' => $this->config->scope,
        ], 'Token refresh');
    }

    /**
     * @param array<string, string> $params
     *
     * @throws AuthenticationException
     * @throws TransportException
     */
    private function requestToken(array $params, string $failureLabel): Token
    {
        $response = $this->httpClient->sendRequest($this->buildTokenRequest($params), $this->config->timeout);

        if ($response->getStatusCode() !== 200) {
            throw new AuthenticationException(sprintf(
                '%s failed with status %d: %s',
                $failureLabel,
                $response->getStatusCode(),
                (string) $response->getBody(),
            ));
        }

        try {
            $data = JsonHelper::decode((string) $response->getBody());
        } catch (JsonException $e) {
            throw new AuthenticationException(sprintf('%s returned invalid JSON: %s', $failureLabel, $e->getMessage()));
        }

        if (!isset($data['access_token'])) {
            throw new AuthenticationException(sprintf('No access token in %s response', strtolower($failureLabel)));
        }

        return Token::fromResponse($data);
    }

    /**
     * @param array<string, string> $params
     */
    private function buildTokenRequest(array $params): RequestInterface
    {
        $baseUrl = rtrim($this->config->baseUrl, '/');

        $request = $this->requestFactory->createRequest(
            'POST',
            $baseUrl . '/v1/oauth/token',
        )
            ->withHeader('Accept', 'application/json')
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded');

        $body = http_build_query($params);

        return $request->withBody(
            $this->streamFactory->createStream($body),
        );
    }
}
