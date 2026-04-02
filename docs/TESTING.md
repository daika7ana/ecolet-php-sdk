# Testing

## PHPUnit Config

Use local `phpunit.xml` to store test credentials and avoid committing secrets.

Example env keys:

- `ECOLET_TEST_MODE`
- `ECOLET_TEST_USERNAME`
- `ECOLET_TEST_PASSWORD`
- `ECOLET_TEST_CLIENT_ID`
- `ECOLET_TEST_CLIENT_SECRET`

## Run All Tests

```bash
php vendor/bin/phpunit -c phpunit.xml
```

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

Smoke tests validate against the live staging API and require valid credentials in `phpunit.xml`.

### Authentication Smoke Test

Tests OAuth password grant flow:

```bash
php vendor/bin/phpunit --filter=AuthSmokeTest -c phpunit.xml
```

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
