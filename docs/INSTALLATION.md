# Installation

## Requirements

- PHP 8.3 or newer
- Composer 2
- `ext-json`

Composer installs Guzzle, the PSR packages, and `daika7ana/simple-collections` (which provides the `Collection` returned by list endpoints) automatically.

## Install

Install with Composer in your project:

```bash
composer require daika7ana/ecolet-php-sdk
```

Create a client:

```php
use Daika7ana\Ecolet\Client;

$client = Client::create();
```
