# Error Handling

Common exceptions exposed by the package:

- `Daika7ana\\Ecolet\\Exceptions\\AuthenticationException`
- `Daika7ana\\Ecolet\\Exceptions\\UnexpectedStatusException`
- `Daika7ana\\Ecolet\\Exceptions\\TransportException`
- `Daika7ana\\Ecolet\\Exceptions\\ValidationException`

`streamMapPoints()` additionally throws `JsonException` when the response stream
is truncated or invalid JSON. An absent, empty, or non-array `mapPoints` value
yields nothing instead of throwing.

`JsonHelper::encode()` can also throw `JsonException` when a request DTO field
contains a string that is not valid UTF-8, for example bodies built by
`AddParcelResource`, `OrderResource`, or `MapPointsResource`.

Exceptions that carry a raw HTTP response also expose the response's numeric
`Retry-After` value in seconds as `$exception->retryAfter`; it is `null` when the
header is absent, non-numeric, or an HTTP date.

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
    // 422 validation failure.
    $message = $e->getMessage();
    $errors = $e->errors;
} catch (UnexpectedStatusException $e) {
    // API returned non-expected HTTP status.
    $status = $e->response->getStatusCode();
} catch (TransportException $e) {
    // Network/transport-level failure.
}
```

## AuthenticationException

- Thrown for invalid credentials, a failed token refresh, or an unauthenticated payload (`200 {"unauthenticated": true}`) from `GET /v1/me`, where the API does not return `401`.
- Exposes the raw PSR-7 response on `$exception->response` when one was in scope (null for local failures such as a missing refresh token).

## ValidationException

- Thrown for HTTP `422` responses.
- Thrown locally by `ParcelDetails::fromArray()` and `CourierPickup::fromArray()` for an unknown `type` value.
- Exposes field errors on `$exception->errors`.
- Exposes the raw PSR-7 response on `$exception->response`; it is null for the local throws above.
- Normalizes both standard `message` payloads and Ecolet `general_error` payloads into the exception message.

## UnexpectedStatusException

- Thrown for non-expected non-`422` HTTP statuses, for example `404` or `500`.
- Exposes the raw PSR response on `$exception->response` so you can inspect the actual status code or body.
