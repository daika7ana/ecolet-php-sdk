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

## Run Smoke Auth Test Only

```bash
php vendor/bin/phpunit --filter=AuthSmokeTest -c phpunit.xml
```
