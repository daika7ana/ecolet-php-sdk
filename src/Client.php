<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet;

use Daika7ana\Ecolet\Auth\InMemoryTokenStore;
use Daika7ana\Ecolet\Auth\PasswordAuthenticator;
use Daika7ana\Ecolet\Auth\Token;
use Daika7ana\Ecolet\Auth\TokenStoreInterface;
use Daika7ana\Ecolet\Exceptions\AuthenticationException;
use Daika7ana\Ecolet\Exceptions\TransportException;
use Daika7ana\Ecolet\Config\ClientConfig;
use Daika7ana\Ecolet\Http\GuzzleHttpClient;
use Daika7ana\Ecolet\Http\HttpClientInterface;
use Daika7ana\Ecolet\Resources\AddParcelResource;
use Daika7ana\Ecolet\Resources\LocationsResource;
use Daika7ana\Ecolet\Resources\MapPointsResource;
use Daika7ana\Ecolet\Resources\OrderResource;
use Daika7ana\Ecolet\Resources\OrderToSendResource;
use Daika7ana\Ecolet\Resources\ServicesResource;
use Daika7ana\Ecolet\Resources\UserResource;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Client
{
    private ClientConfig $config;

    private PasswordAuthenticator $authenticator;

    private ?UserResource $userResource = null;

    private ?ServicesResource $servicesResource = null;

    private ?LocationsResource $locationsResource = null;

    private ?OrderResource $orderResource = null;

    private ?OrderToSendResource $orderToSendResource = null;

    private ?AddParcelResource $addParcelResource = null;

    private ?MapPointsResource $mapPointsResource = null;

    private TokenStoreInterface $tokenStore;

    public function __construct(
        private HttpClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
        ?ClientConfig $config = null,
        ?PasswordAuthenticator $authenticator = null,
        ?TokenStoreInterface $tokenStore = null,
    ) {
        $this->config = $config ?? new ClientConfig();
        $this->tokenStore = $tokenStore ?? new InMemoryTokenStore();

        $storedToken = $this->tokenStore->getToken();
        if ($storedToken !== null) {
            $this->config = $this->config->withToken($storedToken);
        } elseif ($this->config->token !== null) {
            $this->tokenStore->setToken($this->config->token);
        }

        $this->authenticator = $authenticator ?? $this->makeAuthenticator();
    }

    public function getConfig(): ClientConfig
    {
        $this->currentToken();

        return $this->config;
    }

    public function getToken(): ?Token
    {
        return $this->currentToken();
    }

    public function setToken(Token|string $token): self
    {
        $resolvedToken = is_string($token) ? new Token($token) : $token;
        $this->storeToken($resolvedToken);

        return $this;
    }

    public function authenticate(
        string $username,
        string $password,
        string $clientId,
        string $clientSecret,
        string $scope = '',
    ): void {
        $this->config = $this->config->withOAuthCredentials($clientId, $clientSecret, $scope);

        $this->authenticator = $this->makeAuthenticator($username, $password);

        $token = $this->authenticator->authenticate();
        $this->storeToken($token);
    }

    /**
     * @throws AuthenticationException
     * @throws TransportException
     */
    public function refreshToken(): void
    {
        $currentToken = $this->currentToken();

        if ($currentToken?->refreshToken === null || $currentToken->refreshToken === '') {
            throw new AuthenticationException('Cannot refresh token: refresh_token is missing.');
        }

        if ($this->config->clientId === '' || $this->config->clientSecret === '') {
            throw new AuthenticationException('Cannot refresh token: OAuth client credentials are missing.');
        }

        $this->authenticator = $this->makeAuthenticator();

        $token = $this->authenticator->refresh($currentToken->refreshToken);
        $this->storeToken($token);
    }

    public function createRequest(string $method, string $path): RequestInterface
    {
        $uri = $this->config->baseUrl . $path;
        $request = $this->requestFactory->createRequest($method, $uri);

        $token = $this->currentToken();

        if ($token !== null) {
            $request = $this->withAuthorizationHeader($request, $token);
        }

        $request = $request
            ->withHeader('Accept', 'application/json')
            ->withHeader('Accept-Language', $this->config->acceptLanguage);

        return $request;
    }

    public function send(RequestInterface $request): ResponseInterface
    {
        if ($request->hasHeader('Authorization')) {
            $token = $this->currentToken();

            if ($token !== null && $token->isExpired()) {
                $this->refreshToken();
                $token = $this->currentToken();
            }

            if ($token !== null) {
                $request = $this->withAuthorizationHeader($request, $token);
            }
        }

        return $this->httpClient->sendRequest($request);
    }

    public function streamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory;
    }

    public function users(): UserResource
    {
        $this->userResource ??= new UserResource($this);

        return $this->userResource;
    }

    public function services(): ServicesResource
    {
        $this->servicesResource ??= new ServicesResource($this);

        return $this->servicesResource;
    }

    public function locations(): LocationsResource
    {
        $this->locationsResource ??= new LocationsResource($this);

        return $this->locationsResource;
    }

    public function orders(): OrderResource
    {
        $this->orderResource ??= new OrderResource($this);

        return $this->orderResource;
    }

    public function ordersToSend(): OrderToSendResource
    {
        $this->orderToSendResource ??= new OrderToSendResource($this);

        return $this->orderToSendResource;
    }

    public function addParcel(): AddParcelResource
    {
        $this->addParcelResource ??= new AddParcelResource($this);

        return $this->addParcelResource;
    }

    public function mapPoints(): MapPointsResource
    {
        $this->mapPointsResource ??= new MapPointsResource($this);

        return $this->mapPointsResource;
    }

    public static function create(
        ?HttpClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        ?ClientConfig $config = null,
        ?TokenStoreInterface $tokenStore = null,
    ): self {
        $httpClient ??= new GuzzleHttpClient();
        $config ??= ClientConfig::fromEnvironment();

        if ($requestFactory === null || $streamFactory === null) {
            $psr17Factory = new \GuzzleHttp\Psr7\HttpFactory();
            $requestFactory ??= $psr17Factory;
            $streamFactory ??= $psr17Factory;
        }

        return new self($httpClient, $requestFactory, $streamFactory, $config, null, $tokenStore);
    }

    private function makeAuthenticator(string $username = '', string $password = ''): PasswordAuthenticator
    {
        return new PasswordAuthenticator(
            httpClient: $this->httpClient,
            requestFactory: $this->requestFactory,
            streamFactory: $this->streamFactory,
            username: $username,
            password: $password,
            config: $this->config,
        );
    }

    private function currentToken(): ?Token
    {
        $token = $this->tokenStore->getToken();

        if ($token === null) {
            if ($this->config->token !== null) {
                $this->config = $this->config->withoutToken();
            }

            return null;
        }

        if ($this->config->token != $token) {
            $this->config = $this->config->withToken($token);
        }

        return $token;
    }

    private function storeToken(Token $token): void
    {
        $this->tokenStore->setToken($token);
        $this->config = $this->config->withToken($token);
    }

    private function withAuthorizationHeader(RequestInterface $request, Token $token): RequestInterface
    {
        return $request->withoutHeader('Authorization')->withHeader(
            'Authorization',
            sprintf('%s %s', $token->tokenType, $token->accessToken),
        );
    }
}
