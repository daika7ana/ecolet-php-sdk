# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.4.0] - 2026-11-25

### Changed

- `ValidationException::$errors` defaults to `[]` and `$previous` accepts any `Throwable`, aligning the constructor with the sibling SDKs.

## [2.3.0] - 2026-11-23

### Added

- `DTOs\Common\Errors\EcoletError` with `code`, `message`, `id`, `raw`, `context` and `component` fields plus `isValidationRelated()`/`isAuthenticationRelated()`.
- `AuthenticationException`, `ValidationException` and `UnexpectedStatusException` expose the parsed error payload on `$exception->error`, populated from the response body where available.

### Changed

- HTTP exceptions derive `getCode()` from the response status when a response is present, and fall back to the passed code otherwise.
- **Breaking:** HTTP `401` now throws `AuthenticationException` and `400`/`422` throw `ValidationException`; all other unexpected statuses, including `403`, stay `UnexpectedStatusException`.
- **Breaking:** `AuthenticationException`, `ValidationException` and `UnexpectedStatusException` thrown for an HTTP response now carry the HTTP status in `getCode()` instead of `0`.
- **Breaking:** `UnexpectedStatusException::$response` is now nullable and defaults to `null`, so the exception can be raised without a response in scope.

## [2.2.0] - 2026-11-22

### Added

- `Client::withTimeout(?float $timeout)` derives a client sharing the transport, PSR-17 factories, authenticator and token store with a different request timeout budget — no second login, no new transport is built.
- `AuthenticationException` and `ValidationException` now expose the raw PSR-7 response on `$exception->response` when the failure came from an HTTP response (null for local throws). All three, including `UnexpectedStatusException`, expose the numeric `Retry-After` value in seconds on `$exception->retryAfter` (`null` when absent).

## [2.1.0] - 2026-11-20

### Added

- `Client::create()` and `Client::__construct()` accept any PSR-18 `ClientInterface`; a bare client is wrapped in `Psr18HttpClientAdapter`.
- `GuzzleHttpClient::DEFAULT_TIMEOUT`/`DEFAULT_CONNECT_TIMEOUT` and `timeout:`/`connectTimeout:` constructor arguments.

### Changed

- **Breaking:** requests through the bundled transport now time out after 30 s, with a 10 s connect cap. They were previously unbounded (the config timeout defaulted to `null` and the transport applied none). Set `ClientConfig::timeout` to change the request timeout, or `null` is no longer "unbounded".
- **Breaking:** a non-null `ClientConfig::timeout` combined with a bare PSR-18 client throws `InvalidArgumentException`, and a per-call timeout passed to such a client throws too.
- A PSR-18 client that throws a `ClientExceptionInterface` is now wrapped in `TransportException`.

### Removed

- **Breaking:** `Daika7ana\Ecolet\Support\HttpFoundationBridge` and the `symfony/http-foundation` dependency. Convert between PSR-7 and HttpFoundation types in application code.

## [2.0.0] - 2026-11-19

### Changed

- **Breaking:** list-style DTOs and resources now return `Daika7ana\SimpleCollections\Collection` from [`daika7ana/simple-collections`](https://packagist.org/packages/daika7ana/simple-collections) instead of the SDK's own class. Replace any `Daika7ana\Ecolet\DTOs\Common\Collection` type hints and imports with the package class. Existing helpers keep their behavior, and `static`-returning methods still preserve subclasses.
- Collections are now `JsonSerializable`, so `json_encode()` returns the item array instead of `{}`.

### Removed

- `Daika7ana\Ecolet\DTOs\Common\Collection`, superseded by the package.

## [1.2.0] - 2026-11-09

### Added

- `Client::addressBook()` with `getAddresses()`, wrapping `GET /v1/address-book` with optional `page` and `searchQuery` parameters. Returns `AddressBookResult` with a `Collection<Address>` plus the raw `links` and `meta` pagination payload.
- `MapPointsResource::streamMapPoints()`, yielding one `MapPoint` at a time from the response stream so very large payloads are never fully decoded into memory. `getMapPointsRaw()` returns the undecoded PSR-7 response, and `getMapPoints()` builds on it.
- `DTOs\Common\Address` as the shared address shape returned by both the address book and order sender/receiver.

### Changed

- `OrderAddress` now extends `DTOs\Common\Address` instead of declaring the same fields; `OrderAddress::fromArray()` still returns `OrderAddress`.

### Fixed

- `GuzzleHttpClient` disables Guzzle's `http_errors` and redirect handling on the timeout path, matching `sendRequest()`, so `4xx`/`5xx` responses reach `ApiResponseMapper` instead of being wrapped in `TransportException`.
- `Token::fromResponse()` throws `AuthenticationException` when `access_token` is missing or empty, matching `Token::fromArray()`.
- `AddParcelRequest::fromArray()` hoists a lone `parcel` into the `parcels` list when no `parcels` array is present, so the documented single-parcel payload is no longer sent with an empty list.
- `UserResource::getMe()` throws `AuthenticationException` when the API answers `200 {"unauthenticated": true}`, instead of silently returning an empty `User`. The API does not use `401` on this endpoint.

## [1.1.0] - 2026-10-27

### Added

- Refresh fallback also covers transport failures (timeouts, connection errors), not just OAuth error responses: when a refresh fails, the client re-reads the token store and uses the newer token another process wrote.
- `Client::send()` continues with the current token when a refresh is not possible but the token has not actually expired.

### Changed

- `Token::isExpired()` reports a token as expired 30 seconds before `expires_at`, so a request is not sent with a token that could expire in flight. Requests inside that window refresh first, or use the still-valid token when refresh is unavailable.
- `Token::fromArray()` throws `AuthenticationException` when `access_token` is missing or empty, and treats an unparsable `expires_at` as already expired.
- `JsonHelper::decode()` throws `JsonException` when the body is valid JSON but not an object or array (previously a `TypeError`).
- `Collection::offsetExists()` reports offsets that hold `null`, and `offsetGet()` returns `null` for missing offsets instead of emitting a PHP warning.
- `ParcelDetails::fromArray()` and `CourierPickup::fromArray()` reject unknown `type` values with `ValidationException` instead of silently defaulting. This also applies when add-parcel payloads are parsed from order-to-send responses.
- `LocationsResource` and `MapPointsResource` lowercase the country code in request paths and encode path segments with `rawurlencode()`.
- `PasswordAuthenticator` shares a single `requestToken()` path for the password and refresh grants (no public API change).
- `AddParcelResource` routes all three add-parcel operations through a single `post()` helper (no public API change).
- `symfony/http-foundation` moved from `require` to `require-dev` plus `suggest`; install it explicitly to use `HttpFoundationBridge`.
- Dependency ranges: `guzzlehttp/psr7 ^2.0 || ^3.0` (was `^1.7 || ^2.0`), `guzzlehttp/guzzle ^7.0 || ^8.0`, `phpunit/phpunit ^12.0 || ^13.0`.

### Fixed

- `Client::refreshToken()` compared token objects by identity, so a persistent store made the same token look like a refresh performed by another process and could hide a real authentication failure. It now compares access tokens.
- `ApiResponseMapper` passed the HTTP status as the exception code for invalid JSON, while `assertStatus()` throws the same exception with code `0`.
- `ApiResponseMapper::decodeJson()` and `PasswordAuthenticator` only caught `JsonException`, so a `null` or scalar JSON body escaped as a `TypeError`; both now raise a proper exception.
- Documentation: `AddParcelRequest` `parcels` and `additionalServices` are required, not optional; `couponInfo` corrected to `coupon`; `RecipientAddress::county` documented as optional; the HttpFoundation bridge documents its install step.

## [1.0.5] - 2026-06-18

### Added

- Configurable request timeout: `ClientConfig::$timeout` (with `withTimeout()`) as the default, and an optional per-request `Client::send($request, $timeout)` that overrides it.
- `AddParcelRequest::$source` (nullable) for tracking the origin of an order.

### Changed

- `HttpClientInterface::sendRequest()` accepts an optional timeout, applied by `GuzzleHttpClient`.

## [1.0.4] - 2026-05-12

### Added

- `ApiResponseMapper::arrayOrEmpty()`, `listOfArrays()`, and `listOfStrings()` for defensive response parsing.

### Fixed

- Resources no longer fail when a response key is missing or holds the wrong type: locations lists, streets, postal codes, map points, users, orders, and services all go through the new helpers.
- `User::fromArray()` tolerates non-array `forbidden_couriers` / `forbidden_services` values.
- Nullability corrections across order, user, and map-point DTOs, with tests covering the null/missing cases.

## [1.0.3] - 2026-04-23

### Added

- `Collection` helpers: `values()`, `keys()`, `has()`, `hasKey()`, `isEmpty()`, `isNotEmpty()`, `filter()`, `reject()`, `only()`, `mapWithKeys()`, `pluck()`, `push()`, `prepend()`, `each()`, `toArray()`, `all()`, and `ArrayAccess`.

### Changed

- `MapPointsResource::getMapPoints()` returns `MapPointsResponse` (bounding box plus a collection of map points) instead of `MapPointsResult`. `MapPointsPayload` was renamed to `MapPointsResponse`.

### Removed

- `MapPointsResult`, replaced by `MapPointsResponse`.

## [1.0.2] - 2026-04-22

### Added

- Order DTOs: `OrderAddress`, `OrderFee`, `OrderService`, `OrderWithStatuses`, `DeleteOrderResult`.
- Service DTOs: `ServiceConditions`, `ServiceCourier`.
- Location DTO: `StreetsByPostalCodeResult`.
- Expanded `User`, `Order`, `Service`, `Locality`, `Country`, and `County` fields to match the Ecolet schema.

### Fixed

- Locality, street, and postal-code search queries are URL-encoded with `rawurlencode()`, so names containing spaces or slashes no longer break the request.
- `ApiResponseMapper` reads an `error` key as a message fallback for validation responses.
- `OrderStatus` now maps `name`, `real_name`, and `created_at` instead of `id`, `status`, and `description`.
- Stabilized smoke tests for locations and map points.

## [1.0.1] - 2026-04-13

### Changed

- `PasswordAuthenticator` takes a `ClientConfig` and reads OAuth credentials, scope, and base URL from it. Token requests now honor the configured base URL, so staging authentication works through the same config as the rest of the client.

### Removed

- `PasswordAuthenticator` constructor parameters `clientId`, `clientSecret`, `baseUrl`, and `scope` (replaced by `ClientConfig`).

## [1.0.0] - 2026-04-09

### Added

- Initial public release.
- OAuth 2.0 password grant authentication, automatic token refresh, and token restore support.
- PSR-7 / PSR-17 / PSR-18 architecture with a default Guzzle adapter; custom PSR-18 clients can be injected.
- Typed DTOs and `Collection` helpers.
- Production and staging support through `ClientConfig`.
- Optional Symfony / Laravel HttpFoundation bridge.
- Waybill download helpers with file metadata.
- v1 resources: users, services, locations, map points, orders, orders to send.
- v2 add-parcel operations: `reloadForm()`, `sendOrder()`, `saveOrderToSend()`.
- Unit tests, smoke tests, PHPStan, Pint, and CI validation.
