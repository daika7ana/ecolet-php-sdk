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

```php
use Daika7ana\Ecolet\Auth\Token;

$client->setToken(Token::fromArray($cachedTokenData));

// A raw access token string is also accepted for simple cases.
$client->setToken('your-access-token');
```

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
