<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Orders;

final readonly class Order
{
    public function __construct(
        public int $id,
        public string $number,
        public ?string $status = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $order = is_array($data['data'] ?? null) ? $data['data'] : $data;

        return new self(
            id: (int) $order['id'],
            number: (string) ($order['number'] ?? $order['awb'] ?? ''),
            status: isset($order['status']) ? (string) $order['status'] : null,
        );
    }
}
