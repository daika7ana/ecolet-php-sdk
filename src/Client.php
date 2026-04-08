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

        if ($this->config->token !== null) {
            $this->tokenStore->setToken($this->config->token);
        }

        $storedToken = $this->tokenStore->getToken();
        if ($this->config->token === null && $storedToken !== null) {
            $this->config = $this->config->withToken($storedToken);
        }

        $this->authenticator = $authenticator ?? new PasswordAuthenticator(
            httpClient: $this->httpClient,
            requestFactory: $this->requestFactory,
            streamFactory: $this->streamFactory,
            username: '',
            password: '',
            clientId: $this->config->clientId,
            clientSecret: $this->config->clientSecret,
            baseUrl: $this->config->baseUrl,
            scope: $this->config->scope,
        );
    }

    public function getConfig(): ClientConfig
    {
        return $this->config;
    }

    public function getToken(): ?Token
    {
        return $this->tokenStore->getToken() ?? $this->config->token;
    }

    public function setToken(Token|string $token): self
    {
        $resolvedToken = is_string($token) ? new Token($token) : $token;
        $this->tokenStore->setToken($resolvedToken);
        $this->config = $this->config->withToken($resolvedToken);

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

        $this->authenticator = new PasswordAuthenticator(
            httpClient: $this->httpClient,
            requestFactory: $this->requestFactory,
            streamFactory: $this->streamFactory,
            username: $username,
            password: $password,
            clientId: $this->config->clientId,
            clientSecret: $this->config->clientSecret,
            baseUrl: $this->config->baseUrl,
            scope: $this->config->scope,
        );

        $token = $this->authenticator->authenticate();
        $this->tokenStore->setToken($token);
        $this->config = $this->config->withToken($token);
    }

    /**
     * @throws AuthenticationException
     * @throws TransportException
     */
    public function refreshToken(): void
    {
        $currentToken = $this->tokenStore->getToken() ?? $this->config->token;

        if ($currentToken?->refreshToken === null || $currentToken->refreshToken === '') {
            throw new AuthenticationException('Cannot refresh token: refresh_token is missing.');
        }

        if ($this->config->clientId === '' || $this->config->clientSecret === '') {
            throw new AuthenticationException('Cannot refresh token: OAuth client credentials are missing.');
        }

        $this->authenticator = new PasswordAuthenticator(
            httpClient: $this->httpClient,
            requestFactory: $this->requestFactory,
            streamFactory: $this->streamFactory,
            username: '',
            password: '',
            clientId: $this->config->clientId,
            clientSecret: $this->config->clientSecret,
            baseUrl: $this->config->baseUrl,
            scope: $this->config->scope,
        );

        $token = $this->authenticator->refresh($currentToken->refreshToken);
        $this->tokenStore->setToken($token);
        $this->config = $this->config->withToken($token);
    }

    public function createRequest(string $method, string $path): RequestInterface
    {
        $uri = $this->config->baseUrl . $path;
        $request = $this->requestFactory->createRequest($method, $uri);

        if ($this->config->token !== null) {
            $request = $request->withHeader(
                'Authorization',
                sprintf('%s %s', $this->config->token->tokenType, $this->config->token->accessToken),
            );
        }

        $request = $request
            ->withHeader('Accept', 'application/json')
            ->withHeader('Accept-Language', $this->config->acceptLanguage);

        return $request;
    }

    public function send(RequestInterface $request): ResponseInterface
    {
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
}
