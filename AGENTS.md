# AGENTS.md

## Project

Typed PHP 8.3+ SDK for the Ecolet Courier API (`daika7ana/ecolet-php-sdk`,
namespace `Daika7ana\Ecolet`). Library only: no server, no web entrypoint, no
framework in `require`. PSR-18 core, one resource class per API area, readonly
DTOs, enums.

Base URLs are `https://panel.ecolet.ro/api` (production) and
`https://staging.ecolet.ro/api` (`ClientConfig::BASE_URL_STAGING`). The base URL
already ends in `/api`, so paths are `/v1/...` or `/v2/...`; v2 is add-parcel
only.

## Commands

```bash
composer install
composer ci         # validate --strict, pint --test, phpstan, phpunit tests/Unit
composer pint       # style check only, never fixes
composer stan       # PHPStan level 8 over src/
composer test       # full PHPUnit suite, smoke self-skips without credentials
composer test:unit  # tests/Unit only
composer test:smoke # tests/Smoke only, self-skips without credentials
vendor/bin/pint     # the only way to auto-fix style
```

- `composer.lock` is gitignored, so CI resolves fresh; keep `guzzlehttp/psr7` at
  `>=2` because `Client::create()` instantiates `GuzzleHttp\Psr7\HttpFactory`.
- Quality gates: PHPStan level 8 over `src/` (vendor/tests excluded), Laravel
  Pint (`per` preset), PHPUnit strict (`.dist`), `composer validate --strict`.
  CI runs validate, Pint and PHPStan on PHP 8.3 and unit tests on 8.3/8.4/8.5,
  branch `master`; smoke never runs in CI. CI skips every job for commits/PRs starting `docs` or
  `chore`, and for messages containing `[skip ci]` / `[skip-ci]`.

## Layout

`src/` holds `Client.php`, `Config/`, `Auth/`, `Http/`, `Support/`, `Exceptions/`,
`Enums/`, `DTOs/<Area>/` and `Resources/`. `tests/Support/` holds the fakes,
`tests/Unit/` mirrors `src/`, `tests/Smoke/` the live tests; `docs/` holds the
user guides.

## Conventions

- Never import Symfony, Laravel, or another framework into `src/`, `tests/`, or
  docs, and never make one a runtime dependency; framework integration is out of
  scope. No new runtime dependencies without an explicit decision. Requests are
  built through the injected PSR-17 factories and absolute URIs assembled
  internally; callers never pass a URL.
- DTOs are `final readonly` with `fromArray()`; request DTOs add `toArray()`.
- `docs/` is user-facing documentation: behavior changes go in the matching file,
  not just in code. `CHANGELOG.md` is Keep a Changelog, newest first, tags `vX.Y.Z`.
- Commits and PR titles use Conventional Commits (`<type>(<scope>): <subject>`,
  lowercase imperative, no trailing period).

## HTTP Transport And Timeouts

- `Client` builds requests with the injected PSR-17 `RequestFactoryInterface` /
  `StreamFactoryInterface`, consumes PSR-7 `ResponseInterface`, and depends only
  on PSR-18 `Psr\Http\Client\ClientInterface` — never a concrete client.
- `src/Http/HttpClientInterface.php` widens PSR-18 with
  `sendRequest(RequestInterface $request, ?float $timeout = null)`.
  `src/Http/GuzzleHttpClient.php` is the bundled transport (Guzzle `http_errors`
  and redirects disabled, so 4xx/5xx reach the mappers);
  `src/Http/Psr18HttpClientAdapter.php` wraps any other PSR-18 client, rejects a
  non-null per-call timeout with `InvalidArgumentException`, and rethrows a
  foreign `ClientExceptionInterface` as `TransportException` (original as
  `previous`). `TransportException` implements `ClientExceptionInterface`.
- `Client::__construct()` throws `InvalidArgumentException` when
  `ClientConfig::timeout` is non-null and the client is not this package's
  `HttpClientInterface`. `ClientConfig::timeout` is `?float`, default `null`;
  `connectTimeout` exists only on
  `GuzzleHttpClient::__construct(?GuzzleClient $guzzleClient = null, ?float $timeout = null, ?float $connectTimeout = null)`,
  which applies `DEFAULT_TIMEOUT = 30.0` / `DEFAULT_CONNECT_TIMEOUT = 10.0` only
  when it creates its own Guzzle client. Precedence: per-call
  `Client::send($request, $t)` → `ClientConfig::timeout` → transport constructor
  arguments → 30.0/10.0.
- `guzzlehttp/guzzle` and `guzzlehttp/psr7` are runtime dependencies (default
  transport and `GuzzleHttp\Psr7\HttpFactory`), so `Client::create()` needs no
  arguments; no `class_exists` guard, no `suggest` entry.

## API Quirks

- OAuth 2.0 password grant at `/v1/oauth/token` (`grant_type=password`), then
  `refresh_token`; `Client::refreshToken()` catches auth and transport failures
  and re-reads the token store in case another process refreshed first.
- `TokenStoreInterface` (default `InMemoryTokenStore`) is the token source;
  `Token::isExpired()` treats tokens expiring within 30 s as expired, and
  `Token::fromArray()` throws on an empty `access_token`.
- `ClientConfig` carries `baseUrl`, `token`, `clientId`/`clientSecret`, `scope`,
  `acceptLanguage` and `timeout`. `setTestMode()` mutates static state — prefer
  `new ClientConfig(baseUrl: ClientConfig::BASE_URL_STAGING)`.
- Responses go through `Support\ApiResponseMapper`; don't parse status codes ad
  hoc in a resource. `OrderResource::downloadWaybill()` returns a
  `DTOs\Orders\WaybillDocument` wrapping the response stream, and `JsonStream`
  decodes the large map-points payload incrementally.

## Tests

- Unit tests never touch the network. `TestClientFactory::create($handler)`
  returns `[Client, FakeHttpClient]`; `ResponseFixtureFactory` has canned payloads
  and `AddParcelRequestFactory` builds parcel requests.
- Assert method, path, query, body and headers on `$httpClient->lastRequest`;
  every new endpoint needs such a test covering both request and response
  mapping. `phpunit.xml.dist` is strict: output, risky and warning tests fail.
- `tests/Smoke` self-skips unless `ECOLET_TEST_USERNAME`, `ECOLET_TEST_PASSWORD`,
  `ECOLET_TEST_CLIENT_ID`, `ECOLET_TEST_CLIENT_SECRET` are set (put them in the
  gitignored `phpunit.xml`); run with `composer test:smoke` or
  `php vendor/bin/phpunit --group=smoke`.
  `composer ci` must pass before done.
