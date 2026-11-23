# Error Handling

Common exceptions exposed by the package:

- `Daika7ana\\Ecolet\\Exceptions\\EcoletException` (base class; catch it to handle every exception defined by the package)
- `Daika7ana\\Ecolet\\Exceptions\\AuthenticationException`
- `Daika7ana\\Ecolet\\Exceptions\\UnexpectedStatusException`
- `Daika7ana\\Ecolet\\Exceptions\\TransportException` (implements the PSR-18 `ClientExceptionInterface`)
- `Daika7ana\\Ecolet\\Exceptions\\ValidationException`

`streamMapPoints()` additionally throws `JsonException` when the response stream
is truncated or invalid JSON. An absent, empty, or non-array `mapPoints` value
yields nothing instead of throwing.

`JsonHelper::encode()` can also throw `JsonException` when a request DTO field
contains a string that is not valid UTF-8, for example bodies built by
`AddParcelResource`, `OrderResource`, or `MapPointsResource`.

Exceptions that carry a raw HTTP response also expose the response's numeric
`Retry-After` value in seconds as `$exception->retryAfter`; it is `null` when the
header is absent, non-numeric, or an HTTP date. `UnexpectedStatusException::$response`
is nullable and defaults to `null`, so an unexpected status can be raised without a
response in scope.

## Status to exception mapping

| HTTP status | Exception | Note |
|-------------|-----------|------|
| `401` | `AuthenticationException` | `$exception->error` carries the parsed payload |
| `400`, `422` | `ValidationException` | Field errors on `$exception->errors` |
| other non-2xx | `UnexpectedStatusException` | Includes `403`; `$exception->error->isAuthenticationRelated()` is `true` |
| 2xx with unparseable body | `UnexpectedStatusException` | `code` is the response status |

The status alone selects the class. `EcoletError` predicates are consumer hints
and never change which exception is thrown.

Every HTTP-level throw passes the response status as `$exception->getCode()`
(`0` only when no response is in scope).

Example handling:

```php
use Daika7ana\Ecolet\Exceptions\AuthenticationException;
use Daika7ana\Ecolet\Exceptions\TransportException;
use Daika7ana\Ecolet\Exceptions\UnexpectedStatusException;
use Daika7ana\Ecolet\Exceptions\ValidationException;

try {
    $client->authenticate(...);
    $user = $client->users()->getMe();
} catch (AuthenticationException $e) {
    // Invalid credentials or OAuth client details.
} catch (ValidationException $e) {
    // 400/422 validation failure.
    $message = $e->getMessage();
    $errors = $e->errors;
} catch (UnexpectedStatusException $e) {
    // API returned non-expected HTTP status.
    $status = $e->response?->getStatusCode();
} catch (TransportException $e) {
    // Network/transport-level failure.
}
```

## AuthenticationException

- Thrown for invalid credentials, a failed token refresh, or an unauthenticated payload (`200 {"unauthenticated": true}`) from `GET /v1/me`, where the API does not return `401`.
- Exposes the raw PSR-7 response on `$exception->response` when one was in scope (null for local failures such as a missing refresh token).
- Exposes the parsed error payload on `$exception->error` (an `EcoletError`) when the failure came from an HTTP error response. A `401` with a non-JSON body has the fallback message `Ecolet API error.`.
- The token endpoint (`PasswordAuthenticator`) throws it for any non-`200` response, not only `401`.

## ValidationException

- Thrown for HTTP `400` and `422` responses.
- Thrown locally by `ParcelDetails::fromArray()` and `CourierPickup::fromArray()` for an unknown `type` value.
- Exposes field errors on `$exception->errors`.
- Exposes the raw PSR-7 response on `$exception->response`; it is null for the local throws above.
- Exposes the parsed error payload on `$exception->error` (an `EcoletError`) with the response status as the fallback `code`, the raw payload, and the field errors.
- Normalizes both standard `message` payloads and Ecolet `error`/`general_error` payloads into the exception message.

## UnexpectedStatusException

- Thrown for non-expected statuses other than `400`, `401` and `422`, for example `403`, `404` or `500`.
- Exposes the raw PSR response on `$exception->response` so you can inspect the actual status code or body; it is nullable.
- Exposes the parsed error payload on `$exception->error` (an `EcoletError`) with the HTTP status as `code` when the response body has no `code`.

## EcoletError

`Daika7ana\\Ecolet\\DTOs\\Common\\Errors\\EcoletError` is the shared error DTO carried by
the exceptions above:

- `code` (HTTP status or the payload `code`), `message`, `id` (`id` or `error_id`, empty string when absent), `raw` (decoded payload), `context`, `component`.
- `context` and `component` are carrier-defined: they are populated when the API payload includes those keys and are `null` otherwise; do not rely on them being present.
- `message` falls back through `message`, `error`, `general_error`, then `Ecolet API error.` (also used when the body is not JSON).
- `isValidationRelated()` is true for code `422` or a non-empty `errors` array. Known limit: a `400` without an `errors` array returns `false`, even though `400` throws `ValidationException`.
- `isAuthenticationRelated()` is true for code `401`/`403` or a message containing `auth`, `password`, `credential`, `token`, `unauthenticated`, `logged user`, or `access rights`.
