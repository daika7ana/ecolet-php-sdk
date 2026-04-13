<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Auth;

use Daika7ana\Ecolet\Config\ClientConfig;
use Daika7ana\Ecolet\Exceptions\AuthenticationException;
use Daika7ana\Ecolet\Exceptions\TransportException;
use Daika7ana\Ecolet\Http\HttpClientInterface;
use Daika7ana\Ecolet\Support\JsonHelper;
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
        $request = $this->buildTokenRequest([
            'grant_type' => 'password',
            'username' => $this->username,
            'password' => $this->password,
            'client_id' => $this->config->clientId,
            'client_secret' => $this->config->clientSecret,
            'scope' => $this->config->scope,
        ]);

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (TransportException $e) {
            throw $e;
        }

        if ($response->getStatusCode() !== 200) {
            $payload = (string) $response->getBody();

            throw new AuthenticationException(
                sprintf('Authentication failed with status %d: %s', $response->getStatusCode(), $payload),
            );
        }

        $data = JsonHelper::decode((string) $response->getBody());

        if (!isset($data['access_token'])) {
            throw new AuthenticationException('No access token in response');
        }

        return Token::fromResponse($data);
    }

    /**
     * @throws AuthenticationException
     * @throws TransportException
     */
    public function refresh(string $refreshToken): Token
    {
        $request = $this->buildTokenRequest([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $this->config->clientId,
            'client_secret' => $this->config->clientSecret,
            'scope' => $this->config->scope,
        ]);

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (TransportException $e) {
            throw $e;
        }

        if ($response->getStatusCode() !== 200) {
            $payload = (string) $response->getBody();

            throw new AuthenticationException(
                sprintf('Token refresh failed with status %d: %s', $response->getStatusCode(), $payload),
            );
        }

        $data = JsonHelper::decode((string) $response->getBody());

        if (!isset($data['access_token'])) {
            throw new AuthenticationException('No access token in refresh response');
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
