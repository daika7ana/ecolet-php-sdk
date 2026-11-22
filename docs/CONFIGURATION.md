# Configuration

## Design Principles

- The package is PSR-first (PSR-7, PSR-17, PSR-18) and framework-agnostic by default.

## Base URL Selection

When no custom config is passed to `Client::create()`, the package uses **production** by default.

Base URL selection is controlled through `ClientConfig`.

- **Default (test mode disabled):** Production base URL
- **Test mode enabled:** Staging base URL

To switch the default to staging for tests:

```php
use Daika7ana\Ecolet\Config\ClientConfig;

ClientConfig::setTestMode(true);  // Staging for every client created afterwards
```

This static flag applies to every client created later in the same process, so application code should use an explicit base URL instead:

```php
use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Config\ClientConfig;

$client = Client::create(config: new ClientConfig(baseUrl: ClientConfig::BASE_URL_STAGING));
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

## `ClientConfig::fromEnvironment()`

Despite the name, this factory reads no environment variables. It only returns a config pointed at the staging base URL when test mode is enabled with `ClientConfig::setTestMode(true)`, and at production otherwise. Credentials and a custom base URL still have to be passed explicitly.

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

## Timeouts

Timeouts are in seconds and apply to the HTTP transfer only, not to parsing the response. `null` means no explicit timeout: the bundled Guzzle transport then uses its own defaults (30s request, 10s connect).

`ClientConfig::timeout` is `null` by default. Precedence: the per-call argument → `ClientConfig::timeout` → the transport's own default (`GuzzleHttpClient` uses 30 s to respond and 10 s to connect unless you pass `timeout:`/`connectTimeout:`; an injected `GuzzleClient` keeps its own configuration). A bare PSR-18 client keeps its own timeout and cannot receive the per-call argument — passing one throws `InvalidArgumentException`; combining a bare client with a non-null `ClientConfig::timeout` also throws.

```php
// Override the transport default for every request made through this client.
$config = new ClientConfig(timeout: 30.0);

// Immutable variant.
$config = $config->withTimeout(5.0);

// Per-request override; wins over the config value.
$client->send($request, 2.5);
```

When some calls need a different budget than the rest, derive a client:
`$other = $client->withTimeout(30.0);` returns a new client sharing the
transport, the PSR-17 factories, the authenticator and the token store, so no
new transport is built and no second login happens; the original client keeps
its timeout. `withTimeout(null)` returns to the transport default.

## Default HTTP Client (Guzzle)

When you call `Client::create()` without passing a custom client, the package uses the built-in Guzzle-backed adapter and PSR-17 factories.

## Custom PSR-18 Client Injection

You can inject any PSR-18 client; the SDK wraps it, or pass an `HttpClientInterface` to keep per-request timeouts:

```php
use Daika7ana\Ecolet\Client;
use GuzzleHttp\Psr7\HttpFactory;

$factory = new HttpFactory();

/** @var \Psr\Http\Client\ClientInterface $psr18 */
$client = Client::create(
    httpClient: $psr18,
    requestFactory: $factory,
    streamFactory: $factory,
);
```

## Manual Token Injection

```php
use Daika7ana\Ecolet\Auth\Token;

$client->setToken('your-access-token');

$client->setToken(Token::fromArray($cachedTokenData));
```

If you are managing `ClientConfig` directly, you can also clear its stored token explicitly:

```php
$config = $config->withoutToken();
```

## Token Storage Behavior

- By default, the client uses an in-memory token store (`InMemoryTokenStore`). It lives as long as the `Client` object, so under PHP-FPM it is lost after each request and the password grant runs again.
- You can provide a custom token store by passing `tokenStore` to `Client::create(...)`; use one that outlives the request to reuse a token across requests and processes.
- On authentication or refresh, the token is written to both runtime config and token store.
