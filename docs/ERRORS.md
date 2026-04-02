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

try {
    $client->authenticate(...);
    $user = $client->users()->getMe();
} catch (AuthenticationException $e) {
    // Invalid credentials or OAuth client details.
} catch (UnexpectedStatusException $e) {
    // API returned non-expected HTTP status.
} catch (TransportException $e) {
    // Network/transport-level failure.
}
```
