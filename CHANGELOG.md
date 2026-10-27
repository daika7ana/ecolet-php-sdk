# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
