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

By default, `Client::create()` uses **production** base URL. Test mode is disabled by default.

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

## 5. Optional: Use Staging Environment

**Via test mode flag (global):**

```php
use Daika7ana\Ecolet\Config\ClientConfig;
use Daika7ana\Ecolet\Client;

ClientConfig::setTestMode(true);  // Enable staging
$client = Client::create();
```

**Via explicit base URL (no global state):**

```php
use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\Config\ClientConfig;

$config = new ClientConfig(baseUrl: ClientConfig::BASE_URL_STAGING);
$client = Client::create(config: $config);
```

## 6. Add Parcel Example (Optional)

To get pricing and test a shipment:

```php
use Daika7ana\Ecolet\DTOs\{AddParcelRequest, RecipientAddress, ParcelDetails, CourierInfo, CourierPickup};

$request = new AddParcelRequest(
    sender: new RecipientAddress(
        name: 'My Company',
        country: 'ro',
        county: 'Bucharest',
        locality: 'Bucharest',
        localityId: 1,
        postalCode: '010101',
        streetName: 'Main',
        streetNumber: '123',
        contactPerson: 'John Doe',
        email: 'john@example.com',
        phone: '0212345678',
    ),
    receiver: new RecipientAddress(
        name: 'Customer Name',
        country: 'ro',
        county: 'Constanta',
        locality: 'Constanta',
        localityId: 3150,
        postalCode: '900003',
        streetName: 'Beach Road',
        streetNumber: '456',
        contactPerson: 'Customer',
        email: 'customer@example.com',
        phone: '0214824089',
    ),
    parcel: new ParcelDetails(
        type: 'package',
        weight: 1000,    // grams
        content: 'Books',
    ),
    courier: new CourierInfo(
        pickup: new CourierPickup(type: 'courier'),
    ),
);

$result = $client->addParcel()->reloadForm($request);

// Access pricing with type safety
if ($result->isFormResponse()) {
    foreach ($result->formResponse->pricing->pricesGross as $service => $price) {
        echo "$service: " . ($price / 100) . " RON\n";
    }
}
```

See [DTOS.md](DTOS.md) for complete DTO documentation.

## 7. Smoke Test

```bash
php vendor/bin/phpunit --filter=AuthSmokeTest -c phpunit.xml
```
