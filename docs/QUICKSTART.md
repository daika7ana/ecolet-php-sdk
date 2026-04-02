# Quickstart

This guide shows the fastest path to authenticate and call your first endpoint.

## 1. Install

```bash
composer require daika7ana/ecolet-php-api
```

## 2. Create a Client

```php
use Daika7ana\Ecolet\Client;

$client = Client::create();
```

`Client::create()` resolves base URL from `ECOLET_TEST_MODE` when no custom config is passed.

## 3. Authenticate

```php
$client->authenticate(
    username: 'user@example.com',
    password: 'your-password',
    clientId: 'your-client-id',
    clientSecret: 'your-client-secret',
    scope: ''
);
```

## 4. Call First Endpoint

```php
$user = $client->users()->getMe();

echo $user->email;
```

## 5. Optional: Force Staging or Production

```php
use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Config\ClientConfig;

$config = new ClientConfig(baseUrl: ClientConfig::BASE_URL_STAGING);
$client = Client::create(config: $config);
```

## 6. Smoke Test

```bash
php vendor/bin/phpunit --filter=AuthSmokeTest -c phpunit.xml
```
