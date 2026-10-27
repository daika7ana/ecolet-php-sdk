# Authentication

The package supports OAuth password grant against Ecolet token endpoint.

## Required Inputs

- Ecolet account email
- Ecolet account password
- OAuth `client_id`
- OAuth `client_secret`
- Optional `scope`

## Example

```php
$client->authenticate(
    username: 'user@example.com',
    password: 'your-password',
    clientId: 'your-client-id',
    clientSecret: 'your-client-secret',
    scope: ''
);
```

On success, the token is stored in client config and sent automatically as `Authorization: Bearer ...` for subsequent requests.

## Reading the Current Token

```php
$token = $client->getToken();

if ($token !== null) {
    echo $token->accessToken;
}
```

`Client::getToken()` returns the current `Daika7ana\Ecolet\Auth\Token` from the token store/runtime config, or `null` if the client is not authenticated yet.

## Restoring a Cached Token

Refresh needs the OAuth client credentials, so pass them in the config when you restore a token:

```php
use Daika7ana\Ecolet\Auth\Token;
use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Config\ClientConfig;

$config = new ClientConfig(
    clientId: 'your-client-id',
    clientSecret: 'your-client-secret',
);
$client = Client::create(config: $config);

$client->setToken(Token::fromArray($cachedTokenData));

// A raw access token string is also accepted for simple cases.
$client->setToken('your-access-token');
```

Without `clientId` and `clientSecret`, `refreshToken()` throws `AuthenticationException` when the restored token expires.

`Token::fromArray()` throws `AuthenticationException` when `access_token` is missing or empty, so a corrupted cache entry fails at restore time instead of producing requests without a usable bearer token. An `expires_at` value that cannot be parsed is treated as already expired, so the next request refreshes instead of sending a stale token forever.

## Token Storage

The built-in `InMemoryTokenStore` lives as long as the `Client` object. Under PHP-FPM that is a single request, so each request logs in again. Pass a `TokenStoreInterface` that outlives the request (session, cache, database) to reuse one token across requests and processes. `Client::create(tokenStore: $store)` accepts any implementation.

## Clearing a Stored Token

If you are working with `ClientConfig` directly and need to remove its stored token snapshot, use:

```php
$config = $config->withoutToken();
```

This only clears the token stored on the config object itself. If you are also using a custom token store, clear that store as well.

## Refresh Token Flow

When the current token includes a `refresh_token`, you can request a new token:

```php
$client->refreshToken();
```

The client updates both its internal token store and runtime config with the refreshed token.

## Token Expiry

A token counts as expired 30 seconds before its `expires_at`, so a request is not sent with a token that expires while it is in flight. Requests inside that window trigger `refreshToken()`, which needs a `refresh_token` and the OAuth client credentials; without them the request throws `AuthenticationException` instead of being sent with a nearly expired token.

If a refresh fails, the client re-reads the token store once: when another process has already written a different, still-valid token, that token is used and the original error is swallowed. Otherwise the error is rethrown.
