# Installation

## Requirements

- PHP 8.3 or newer
- Composer 2
- `ext-json`

Composer installs Guzzle and the PSR packages automatically.

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
