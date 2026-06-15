# OpenCode Repository Guide

This is a PHP 8.3+ library — a typed SDK for the Ecolet Courier API. It is not an application; there is no server, no web entrypoint, and no framework dependency by default.

## Developer Commands

Use Composer scripts. CI runs the same sequence on PHP 8.3–8.5:

```bash
composer install
composer ci      # pint -> stan -> test:unit
composer pint    # Pint in test mode only (will not auto-fix)
composer stan    # PHPStan level 8 over src/
composer test    # all tests including smoke tests
composer test:unit
```

Pass PHPUnit arguments through `composer test` with `--`:

```bash
composer test -- --filter=LocationsSmokeTest
composer test -- tests/Unit/Resources/LocationsResourceTest.php
```

## CI Pipeline

`.github/workflows/ci.yml` runs three separate jobs:

1. `composer pint` — style check
2. `composer stan` — static analysis
3. `composer test:unit` — unit tests across PHP 8.3, 8.4, 8.5

Smoke tests are **not** run in CI because they hit the live staging API.

## Testing

- Default config is `phpunit.xml`. It is gitignored; use it for local credentials.
- Template config is `phpunit.xml.dist` (empty env values).
- Smoke tests are annotated with `@group smoke` and use the live staging API.
- They require these env vars: `ECOLET_TEST_USERNAME`, `ECOLET_TEST_PASSWORD`, `ECOLET_TEST_CLIENT_ID`, `ECOLET_TEST_CLIENT_SECRET`.
- If credentials are missing, the smoke helper trait skips the test gracefully.

Run smoke tests:

```bash
php vendor/bin/phpunit --group=smoke -c phpunit.xml
php vendor/bin/phpunit --filter=AddParcelWorkflowSmokeTest -c phpunit.xml
```

Run unit tests alone:

```bash
composer test:unit
```

## Architecture

- `src/Client.php` is the entrypoint. `Client::create()` returns a production-configured SDK client.
- `src/Config/ClientConfig.php` controls base URL. Production is default; staging is opt-in via explicit config or `ClientConfig::setTestMode(true)`.
- Resources are lazy-initialized in `Client`: `users()`, `services()`, `locations()`, `orders()`, `ordersToSend()`, `addParcel()`, `mapPoints()`.
- Two API versions:
  - `/api/v1` — general resources and auth
  - `/api/v2` — add-parcel operations (`AddParcelResource`)
- PSR-18/7/17 based. Guzzle is the default HTTP adapter. Custom PSR-18 clients can be injected via `Client::create(httpClient: $custom)`.
- Optional `src/Support/HttpFoundationBridge.php` converts between Symfony HttpFoundation and PSR objects without coupling the core to Symfony.

## Code Style & Static Analysis

- `pint.json` uses the `per` preset with extra rules for unused imports, no unneeded braces, left-aligned PHPDoc, and useless concat removal.
- `phpstan.neon` analyzes `src/` at level 8, PHP 8.3, excludes `tests/` and `vendor/`.
- Do not run `vendor/bin/pint` without `--test` in CI context; `composer pint` already adds `--test`.

## Credentials & Secrets

- The checked-in `phpunit.xml` contains real staging credentials and is gitignored. Do not accidentally create a `phpunit.xml` from the dist file without noticing the difference.
- `ClientConfig::setTestMode()` mutates global state; prefer passing an explicit `ClientConfig(baseUrl: ClientConfig::BASE_URL_STAGING)` to `Client::create()` if you need scoped staging behavior.
