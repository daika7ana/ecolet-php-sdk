# Ecolet PHP API Wrapper

A PSR-first, framework-agnostic PHP client for the Ecolet API.

The package is designed to work well in Laravel and other PHP frameworks while keeping the core contract standards-based through PSR-7, PSR-17, and PSR-18.

## Features

- OAuth password-grant authentication
- Refresh-token support
- PSR-18 HTTP client abstraction with Guzzle as the default adapter
- Environment-aware production/staging base URL selection
- Typed DTOs for supported resources
- Optional Symfony HttpFoundation bridge utilities
- PHPUnit smoke and unit test coverage

## Requirements

- PHP `>= 8.3`
- PSR-7 / PSR-17 / PSR-18 compatible environment

## Installation

```bash
composer require daika7ana/ecolet-php-api
```

## Quick Start

```php
use Daika7ana\Ecolet\Client;

$client = Client::create();

$client->authenticate(
	username: 'user@example.com',
	password: 'your-password',
	clientId: 'your-client-id',
	clientSecret: 'your-client-secret',
	scope: ''
);

$user = $client->users()->getMe();

echo $user->email;
```

## Base URL Selection

When no custom config is passed, the client resolves the Ecolet base URL from `ECOLET_TEST_MODE`:

- `true`, `1`, `yes`, `on` => staging
- anything else => production

Available constants:

- `ClientConfig::BASE_URL_PRODUCTION`
- `ClientConfig::BASE_URL_STAGING`

Example:

```php
use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Config\ClientConfig;

$config = new ClientConfig(baseUrl: ClientConfig::BASE_URL_STAGING);
$client = Client::create(config: $config);
```

## Authentication

The package currently implements the OAuth password grant flow.

Required values:

- Ecolet account email
- Ecolet account password
- OAuth `client_id`
- OAuth `client_secret`

Refresh example:

```php
$client->refreshToken();
```

If you already have a token, you can restore it directly:

```php
use Daika7ana\Ecolet\Auth\Token;

$token = Token::fromArray($cachedTokenData);
$client->setToken($token);
```

## HTTP Client and Token Storage

- `Client::create()` uses a Guzzle-backed adapter by default
- you can inject your own `HttpClientInterface` implementation
- the default token store is in-memory
- you can pass a custom `TokenStoreInterface` implementation to persist tokens

Example custom client injection:

```php
use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Http\HttpClientInterface;

/** @var HttpClientInterface $customClient */
$client = Client::create(httpClient: $customClient);
```

## Supported Resources

### v1 resources

- `users()->getMe()`
- `services()->getServices()`
- `locations()->getCountries()`
- `locations()->getCounties()`
- `locations()->searchLocalities()`
- `locations()->searchStreets()`
- `locations()->searchStreetPostalCodes()`
- `locations()->searchStreetsByPostalCode()`
- `orders()->getOrder()`
- `orders()->deleteOrder()`
- `orders()->downloadWaybill()`
- `orders()->getStatusesForManyOrders()`
- `ordersToSend()->getOrderToSend()`
- `mapPoints()->getMapPoints()`

### v2 resources

- `addParcel()->reloadForm()`
- `addParcel()->sendOrder()`
- `addParcel()->saveOrderToSend()`

## API Versioning Notes

- General resources and authentication flow use `/api/v1`
- Add Parcel operations are intentionally implemented on `/api/v2` only
- v1 Add Parcel endpoints are intentionally excluded
- Authorization-code OAuth flow is intentionally out of scope

## Framework Interoperability

The package is framework-agnostic by design.

If you need Symfony/Laravel HttpFoundation interop, use the optional `HttpFoundationBridge` helper. This is additive support only and does not replace the PSR-based core API.

## Testing

Run the full test suite:

```bash
php vendor/bin/phpunit -c phpunit.xml
```

Run only the live auth smoke test:

```bash
php vendor/bin/phpunit --filter=AuthSmokeTest -c phpunit.xml
```

Useful local test env keys:

- `ECOLET_TEST_MODE`
- `ECOLET_TEST_USERNAME`
- `ECOLET_TEST_PASSWORD`
- `ECOLET_TEST_CLIENT_ID`
- `ECOLET_TEST_CLIENT_SECRET`

## Documentation

- [Quickstart](docs/QUICKSTART.md)
- [Installation](docs/INSTALLATION.md)
- [Configuration](docs/CONFIGURATION.md)
- [Authentication](docs/AUTHENTICATION.md)
- [Resources](docs/RESOURCES.md)
- [Testing](docs/TESTING.md)
- [Error Handling](docs/ERRORS.md)
- [Documentation Index](docs/README.md)
