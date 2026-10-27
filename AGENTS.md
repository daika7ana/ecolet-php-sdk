# OpenCode Repository Guide

PHP 8.3+ library — a typed SDK for the Ecolet Courier API. No server, no web
entrypoint, no framework dependency in `require`.

## Commands

```bash
composer ci        # pint -> stan -> test:unit (exactly what CI runs)
composer pint      # style check only — never fixes
composer stan      # PHPStan level 8 over src/
composer test      # every test, smoke included
composer test:unit # tests/Unit only
vendor/bin/pint    # the only way to actually fix style

composer test -- tests/Unit/Resources/LocationsResourceTest.php
composer test -- --filter=LocationsResourceTest
```

- `composer.lock` is gitignored: nothing is pinned, CI resolves fresh on every
  run. Keep `composer.json` constraints honest — `guzzlehttp/psr7` must stay
  `>=2` because `Client::create()` instantiates `GuzzleHttp\Psr7\HttpFactory`
  (2.x+ only).
- CI jobs: `pint` and `stan` on PHP 8.3 only; `test:unit` on 8.3/8.4/8.5.
  Smoke tests never run in CI (live staging API).

## Testing

- `tests/Unit/` mirrors `src/`. Build tests with
  `Daika7ana\Ecolet\Tests\Support\FakeHttpClient` (handler returns a real
  `GuzzleHttp\Psr7\Response`) and `GuzzleHttp\Psr7\HttpFactory` — don't mock
  Guzzle or hand-roll PSR stubs.
- `tests/Smoke/` skips itself when `ECOLET_TEST_USERNAME`,
  `ECOLET_TEST_PASSWORD`, `ECOLET_TEST_CLIENT_ID`,
  `ECOLET_TEST_CLIENT_SECRET` are unset. `phpunit.xml` is gitignored and holds
  real credentials; `phpunit.xml.dist` is the empty template PHPUnit falls back
  to. Run them with `php vendor/bin/phpunit -c phpunit.xml --group=smoke`.

## Architecture

- `src/Client.php` is the only entrypoint; `Client::create()` wires Guzzle +
  `HttpFactory` unless factories are injected. Resources are lazy:
  `users()`, `services()`, `locations()`, `orders()`, `ordersToSend()`,
  `addParcel()`, `mapPoints()`.
- The base URL already ends in `/api`
  (`ClientConfig::BASE_URL_PRODUCTION|STAGING`), so resource paths are
  `/v1/...` or `/v2/...`. v1 = general + OAuth, v2 = add-parcel only.
- Auth lives in `src/Auth/`. `Client::send()` refreshes through the injected
  `TokenStoreInterface`; the cross-process refresh fallback (catches auth *and*
  transport failures) and the 30s expiry skew are deliberate and covered in
  `ClientTest` — read those tests before touching `send()`/`refreshToken()`.
  `Token::fromArray()` throws on an empty `access_token`.
- `src/Support/HttpFoundationBridge.php` is the only Symfony-coupled file, and
  `symfony/http-foundation` is `require-dev` only: never reference Symfony from
  core paths or docs examples, point users to installing it themselves.
- `ClientConfig::setTestMode()` mutates static state; prefer
  `new ClientConfig(baseUrl: ClientConfig::BASE_URL_STAGING)` (what the smoke
  trait does).

## Conventions

- `docs/` is user-facing API documentation. Behavior changes go in the matching
  file (AUTHENTICATION, CONFIGURATION, DTOS, RESOURCES, …), not just in code.
- `CHANGELOG.md` is Keep a Changelog, newest version first, one
  `## [x.y.z] - YYYY-MM-DD` section per release; tags are `vX.Y.Z`. Do not add
  the link-reference block at the bottom.
