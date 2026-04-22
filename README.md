# Ecolet PHP SDK

<div align="center">

[![CI](https://github.com/daika7ana/ecolet-php-sdk/actions/workflows/ci.yml/badge.svg)](https://github.com/daika7ana/ecolet-php-sdk/actions/workflows/ci.yml)
[![PHP 8.3+](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat-square&logo=php)](https://www.php.net/)
[![PHPUnit](https://img.shields.io/badge/PHPUnit-12.x-0A7BBB?style=flat-square)](docs/TESTING.md)
[![Smoke Tests](https://img.shields.io/badge/Smoke%20Tests-Live%20API-orange?style=flat-square)](docs/TESTING.md)
[![PSR Standards](https://img.shields.io/badge/PSR-7%2F17%2F18-blue?style=flat-square)](https://www.php-fig.org/)
[![License](https://img.shields.io/badge/license-GPL--3.0--or--later-green?style=flat-square)](LICENSE)

A modern, type-safe PHP SDK for the **Ecolet Courier API**

</div>

> 🚀 Framework-agnostic, OAuth-powered, fully typed with DTOs, and production-ready.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [Authentication](#authentication)
- [Supported Resources](#supported-resources)
- [Framework Support](#framework-support)
- [Testing](#testing)
- [Documentation](#documentation)

## Features

### Core Strengths

- ✅ **OAuth 2.0 Password Grant** — Industry-standard authentication
- ✅ **Automatic Token Refresh** — No manual token management needed
- ✅ **Token Inspection & Restore** — Read the current token or inject a cached one
- ✅ **PSR-18 HTTP Client** — Pluggable, with Guzzle adapter by default
- ✅ **Explicit Environment Selection** — Production by default, staging via `ClientConfig` when needed
- ✅ **Fully Typed DTOs** — Type-safe request/response handling
- ✅ **Static Analysis with PHPStan** — Strict type checks for the `src/` codebase
- ✅ **Iterable Collections** — `first`, `last`, `get`, `values`, `map`, `mapWithKeys`, `pluck`
- ✅ **Waybill Helpers** — Filename, contents, and download headers on `WaybillDocument`
- ✅ **Symfony/Laravel Bridge** — Optional `HttpFoundationBridge` for seamless integration
- ✅ **Comprehensive Tests** — Unit and smoke test suites included

### Perfect For

- 🎯 Laravel applications or any PHP framework
- 🎯 Microservices and standalone PHP projects
- 🎯 Type-safe courier management workflows
- 🎯 Production deployments with full test coverage

## Requirements

- **PHP 8.3+** with `json` and `curl` extensions
- PSR-7 / PSR-17 / PSR-18 compatible environment

## Installation

### Via Composer

```bash
composer require daika7ana/ecolet-php-sdk
```

## Quick Start

Working with the Ecolet API is straightforward:

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

That's it! You've got a fully authenticated client ready to fetch courier data.

## Configuration

### Base URL & Environment

By default, the client runs against **production**. To enable staging globally:

```php
use Daika7ana\Ecolet\Config\ClientConfig;

ClientConfig::setTestMode(true);  // Enable staging
```

### Explicit Base URL

For explicit control without global state:

```php
use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Config\ClientConfig;

// Staging
$config = new ClientConfig(baseUrl: ClientConfig::BASE_URL_STAGING);
$client = Client::create(config: $config);

// Or production (the default)
$config = new ClientConfig(baseUrl: ClientConfig::BASE_URL_PRODUCTION);
$client = Client::create(config: $config);
```

## Authentication

### OAuth 2.0 Password Grant

The package uses industry-standard OAuth 2.0 password grant flow. You'll need:

- Ecolet account email
- Ecolet account password
- OAuth `client_id`
- OAuth `client_secret`

### Automatic Token Refresh

Tokens refresh automatically when expired. Or refresh manually:

```php
$client->refreshToken();
```

### Access Current Token

```php
$token = $client->getToken();
$accessToken = $token?->accessToken;
```

### Token Restoration

Already have a cached token? Restore it directly:

```php
use Daika7ana\Ecolet\Auth\Token;

$token = Token::fromArray($cachedTokenData);
$client->setToken($token);
```

## Supported Resources

### v1 Resources (General API)

- `users()->getMe()` — Get authenticated user info
- `services()->getServices()` — List available services
- `locations()->getCountries()` — Get countries
- `locations()->getCounties()` — Get counties for country
- `locations()->searchLocalities()` — Search localities
- `locations()->searchStreets()` — Search streets
- `locations()->searchStreetPostalCodes()` — Get postal codes
- `locations()->searchStreetsByPostalCode()` — Search streets by postal code with validation metadata
- `orders()->getOrder()` — Retrieve order details
- `orders()->deleteOrder()` — Cancel an order
- `orders()->downloadWaybill()` — Get waybill document
- `orders()->getStatusesForManyOrders()` — Batch status check
- `ordersToSend()->getOrderToSend()` — Get order ready to send
- `mapPoints()->getMapPoints()` — Get pickup points

### v2 Resources (Add Parcel Operations)

- `addParcel()->reloadForm()` — Reload form with defaults
- `addParcel()->sendOrder()` — Submit and send order
- `addParcel()->saveOrderToSend()` — Save order for later

### API Versioning

- General resources and authentication use `/api/v1`
- Add Parcel operations use `/api/v2` exclusively
- v1 Add Parcel endpoints are intentionally excluded
- Authorization-code OAuth flow is out of scope

## Framework Support

### Zero Framework Dependencies

This package works equally well everywhere:

- 🌐 **Laravel** applications
- 🌐 **Symfony** projects
- 🌐 **Standalone PHP** applications
- 🌐 **Microservices**
- 🌐 Any **PHP 8.3+** environment

### Optional: HttpFoundation Bridge

Using Laravel or Symfony? Our optional `HttpFoundationBridge` provides seamless integration without breaking the PSR-based core API.

### Custom HTTP Client

Need a custom HTTP client? Pass any PSR-18 implementation:

```php
use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Http\HttpClientInterface;

/** @var HttpClientInterface $customClient */
$client = Client::create(httpClient: $customClient);
```

### Custom Token Storage

Implement the `TokenStoreInterface` to persist tokens:

```php
use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Auth\TokenStoreInterface;

/** @var TokenStoreInterface $tokenStore */
$client = Client::create(tokenStore: $tokenStore);
```

## Testing

### Run Static Analysis

```bash
composer stan
# or
vendor/bin/phpstan --no-progress
```

PHPStan is configured via `phpstan.neon` and analyzes the `src/` directory at a strict level.

### Run Full Test Suite

```bash
composer test
# or
php vendor/bin/phpunit -c phpunit.xml
```

Pass PHPUnit arguments through Composer with `--`:

```bash
composer test -- --filter=AuthSmokeTest
composer test -- tests/Unit/Resources/LocationsResourceTest.php
```

### Run Specific Tests

```bash
# Run only auth smoke test
php vendor/bin/phpunit --filter=AuthSmokeTest -c phpunit.xml

# Run the combined add-parcel workflow + waybill smoke test
php vendor/bin/phpunit --filter=AddParcelWorkflowSmokeTest -c phpunit.xml

# Run negative staging smoke tests
php vendor/bin/phpunit --filter=AddParcelFailureSmokeTest -c phpunit.xml

# Run all smoke tests
php vendor/bin/phpunit --group=smoke -c phpunit.xml
```

### Run The Same Checks As CI

```bash
composer ci
```

Available Composer scripts:

- `composer pint` runs Pint in test mode
- `composer stan` runs PHPStan against `src/`
- `composer test:unit` runs the unit test suite
- `composer ci` runs the same local checks as CI

Smoke tests hit the live staging API and require valid credentials in `phpunit.xml`.

### Test Environment Variables

Configure these in `phpunit.xml` to enable smoke tests:

| Variable | Purpose |
|----------|---------|
| `ECOLET_TEST_USERNAME` | Test account email |
| `ECOLET_TEST_PASSWORD` | Test account password |
| `ECOLET_TEST_CLIENT_ID` | OAuth client ID |
| `ECOLET_TEST_CLIENT_SECRET` | OAuth client secret |

[See detailed testing docs →](docs/TESTING.md)

## Documentation

Complete guides and references:

| Guide | Purpose |
|-------|---------|
| 📖 [Quickstart](docs/QUICKSTART.md) | Jump right in with working examples |
| 🔧 [Installation](docs/INSTALLATION.md) | Detailed setup instructions |
| ⚙️ [Configuration](docs/CONFIGURATION.md) | Environment setup and options |
| 🔐 [Authentication](docs/AUTHENTICATION.md) | OAuth flow and token management |
| 🔗 [Resources](docs/RESOURCES.md) | Complete resource reference |
| 📦 [DTOs](docs/DTOS.md) | Data Transfer Objects and enums |
| ✅ [Testing](docs/TESTING.md) | Unit and smoke test coverage |
| ❌ [Errors](docs/ERRORS.md) | Exception handling guide |
| 📚 [All Docs](docs/USAGE.md) | Documentation index |

---

<div align="center">

Made with ❤️

**Questions?** [Check the docs](docs/USAGE.md) or [open an issue](../../issues)

</div>
