# Error Handling

Common exceptions exposed by the package:

- `Daika7ana\\Ecolet\\Exceptions\\AuthenticationException`
- `Daika7ana\\Ecolet\\Exceptions\\UnexpectedStatusException`
- `Daika7ana\\Ecolet\\Exceptions\\TransportException`
- `Daika7ana\\Ecolet\\Exceptions\\ValidationException`

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

## ValidationException

- Thrown for HTTP `422` responses.
- Exposes field errors on `$exception->errors`.
- Normalizes both standard `message` payloads and Ecolet `general_error` payloads into the exception message.

## UnexpectedStatusException

- Thrown for non-expected non-`422` HTTP statuses, for example `404` or `500`.
- Exposes the raw PSR response on `$exception->response` so you can inspect the actual status code or body.
