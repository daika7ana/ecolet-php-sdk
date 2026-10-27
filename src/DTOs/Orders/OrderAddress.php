<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Orders;

use Daika7ana\Ecolet\DTOs\Common\Address;

/**
 * Order sender/receiver address. Kept as a distinct class for backward
 * compatibility with the public `Order::$sender` / `Order::$receiver` types.
 */
final readonly class OrderAddress extends Address {}
