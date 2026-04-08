# Configuration

## Design Principles

- The package is PSR-first (PSR-7, PSR-17, PSR-18) and framework-agnostic by default.
- Laravel/Symfony interoperability is additive via optional helpers (for example, `HttpFoundationBridge`).
- Core runtime flow is not coupled to HttpFoundation.

## Base URL Selection

When no custom config is passed to `Client::create()`, the package uses **production** by default.

Base URL selection is controlled through `ClientConfig`.

- **Default (test mode disabled):** Production base URL
- **Test mode enabled:** Staging base URL

To switch to staging globally:

```php
use Daika7ana\Ecolet\Config\ClientConfig;

ClientConfig::setTestMode(true);  // Staging
```

To return to production:

```php
ClientConfig::setTestMode(false);  // Production
```

Production base URL:

```text
https://panel.ecolet.ro/api
```

Staging base URL:

```text
https://staging.ecolet.ro/api
```

## Custom ClientConfig

```php
use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Config\ClientConfig;

$config = new ClientConfig(
    baseUrl: ClientConfig::BASE_URL_PRODUCTION,
    acceptLanguage: 'ro'
);

$client = Client::create(config: $config);
```

## Default HTTP Client (Guzzle)

When you call `Client::create()` without passing a custom client, the package uses the built-in Guzzle-backed adapter.

## Custom PSR-18 Client Injection

You can inject any PSR-18 compatible client adapter that implements the package HTTP contract:

```php
use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Http\HttpClientInterface;

/** @var HttpClientInterface $customClient */
$client = Client::create(httpClient: $customClient);
```

## Manual Token Injection

```php
use Daika7ana\Ecolet\Auth\Token;

$client->setToken('your-access-token');

$client->setToken(Token::fromArray($cachedTokenData));
```

## Token Storage Behavior

- By default, the client uses an in-memory token store (`InMemoryTokenStore`).
- You can provide a custom token store by passing `tokenStore` to `Client::create(...)`.
- On authentication or refresh, the token is written to both runtime config and token store.

## Optional: Coupling with HttpFoundationBridge

Use this only when your app already works with Symfony HttpFoundation objects and you want to bridge to the PSR-based client.

```php
use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Support\HttpFoundationBridge;
use GuzzleHttp\Psr7\HttpFactory;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

$client = Client::create();
$factory = new HttpFactory();

// Symfony Request -> PSR Request
$symfonyInbound = SymfonyRequest::create(
    uri: 'https://example.test/proxy?country=RO',
    method: 'POST',
    content: '{"service_id": 2}'
);

$psrRequest = HttpFoundationBridge::fromSymfonyRequest(
    $symfonyInbound,
    $factory,
    $factory,
);

// You can also build requests directly with the client when calling Ecolet endpoints.
$ecoletRequest = $client->createRequest('GET', '/v1/me');
$ecoletResponse = $client->send($ecoletRequest);

// PSR Response -> Symfony Response
$symfonyResponse = HttpFoundationBridge::toSymfonyResponse($ecoletResponse);
```
