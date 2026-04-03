<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Orders;

use Psr\Http\Message\StreamInterface;

final readonly class WaybillDocument
{
    public function __construct(
        public StreamInterface $stream,
        public ?string $contentType = null,
        public ?string $contentDisposition = null,
    ) {}
}
