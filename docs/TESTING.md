# Testing

## PHPUnit Config

Use local `phpunit.xml` to store test credentials and avoid committing secrets.

Example env keys:

- `ECOLET_TEST_MODE` — defaults to `true` (staging) if not defined
- `ECOLET_TEST_USERNAME`
- `ECOLET_TEST_PASSWORD`
- `ECOLET_TEST_CLIENT_ID`
- `ECOLET_TEST_CLIENT_SECRET`

## Run All Tests

```bash
composer test
composer test -- --filter=LocationsSmokeTest
composer test -- tests/Unit/Resources/LocationsResourceTest.php

php vendor/bin/phpunit -c phpunit.xml
```

Use `--` after `composer test` to forward PHPUnit arguments.

## Run Unit Tests Only

```bash
php vendor/bin/phpunit tests/Unit/ -c phpunit.xml
```

## Run Smoke Tests Only

```bash
php vendor/bin/phpunit --group=smoke -c phpunit.xml
```

## Run Specific Smoke Test

```bash
php vendor/bin/phpunit --filter=ReloadFormSmokeTest -c phpunit.xml
```

## Smoke Tests

Smoke tests validate against the live staging API and require valid credentials in `phpunit.xml`. All smoke tests default `ECOLET_TEST_MODE` to `true` (staging) if not defined.

### Authentication Smoke Test

Tests OAuth password grant flow:

```bash
php vendor/bin/phpunit --filter=AuthSmokeTest -c phpunit.xml
```

### User Resource Smoke Tests

Tests authenticated user endpoint and DTO mapping:

```bash
php vendor/bin/phpunit --filter=UserSmokeTest -c phpunit.xml
```

Validates:
- `getMe()` returns typed `User` DTO
- User email matches authentication credentials

### Services Resource Smoke Tests

Tests services list endpoint:

```bash
php vendor/bin/phpunit --filter=ServicesSmokeTest -c phpunit.xml
```

Validates:
- `getServices()` returns non-empty `Collection<Service>`
- All service items have required properties (id, name, active flag)

### Locations Resource Smoke Tests

Tests location hierarchy and search endpoints:

```bash
php vendor/bin/phpunit --filter=LocationsSmokeTest -c phpunit.xml
```

Validates:
- `getCountries()` returns country list
- `getCounties(countryCode)` returns county list
- `searchLocalities(countryCode, query)` returns matching localities
- `searchStreets(localityId, query)` returns street name strings
- `searchStreetPostalCodes(localityId, streetName)` returns typed `StreetPostalCode` DTOs
- `searchStreetsByPostalCode(countryCode, postalCode)` returns typed `Street` DTOs

### Map Points Resource Smoke Tests

Tests map-points endpoint for pickup locations:

```bash
php vendor/bin/phpunit --filter=MapPointsSmokeTest -c phpunit.xml
```

Validates:
- `getMapPoints(countryCode)` returns typed `MapPointsResult`
- Response contains bounding box and list of `MapPoint` objects
- `destination` query parameter filters by receiver/sender eligibility

### Reload Form Smoke Tests

Tests Add Parcel reload-form endpoint with response validation. Includes:

- **testReloadFormAgainstStagingApi** — Basic connectivity and response type detection
- **testReloadFormResponseContainsPricingInfo** — Validates pricing structure (net/gross prices, VAT, fees)
- **testReloadFormResponseHasServiceStatuses** — Validates service availability statuses per-courier
- **testReloadFormResponseContainsPickupDates** — Validates pickup date availability structure
- **testReloadFormResponseHasNoErrors** — Validates error structure and field-grouped error messages

Run all reload-form tests:

```bash
php vendor/bin/phpunit --filter=ReloadFormSmokeTest -c phpunit.xml
```

### Example: Checking Smoke Test Response Validation

The smoke tests validate the strongly-typed response DTOs:

```php
$result = $client->addParcel()->reloadForm($request);

// Type-safe response access
$this->assertTrue($result->isFormResponse());
$this->assertGreaterThan(0, count($result->formResponse->pricing->statuses));
$this->assertIsArray($result->formResponse->pricing->pricesGross);

// Error structure validation
if ($result->formResponse->hasErrors()) {
    foreach ($result->formResponse->errors as $field => $messages) {
        // Each field has array of message strings
        $this->assertIsArray($messages);
    }
}
```
