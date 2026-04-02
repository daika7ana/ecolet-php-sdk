<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Auth;

use Daika7ana\Ecolet\Exceptions\AuthenticationException;
use Daika7ana\Ecolet\Exceptions\TransportException;
use Daika7ana\Ecolet\Http\HttpClientInterface;
use Daika7ana\Ecolet\Support\JsonHelper;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class PasswordAuthenticator
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
        private string $username,
        private string $password,
        private string $clientId,
        private string $clientSecret,
        private string $baseUrl = 'https://panel.ecolet.ro/api',
        private string $scope = '',
    ) {}

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
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => $this->scope,
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
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => $this->scope,
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
    private function buildTokenRequest(array $params)
    {
        $request = $this->requestFactory->createRequest(
            'POST',
            $this->baseUrl . '/v1/oauth/token',
        )
            ->withHeader('Accept', 'application/json')
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded');

        $body = http_build_query($params);

        return $request->withBody(
            $this->streamFactory->createStream($body),
        );
    }
}
