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

## Refresh Token Flow

When the current token includes a `refresh_token`, you can request a new token:

```php
$client->refreshToken();
```

The client updates both its internal token store and runtime config with the refreshed token.
