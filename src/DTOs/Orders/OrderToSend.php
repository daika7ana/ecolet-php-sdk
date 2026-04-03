<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Orders;

use Daika7ana\Ecolet\DTOs\AddParcel\AddParcelRequest;

final readonly class OrderToSend
{
    public function __construct(
        public int $id,
        public string $status,
        public ?string $error = null,
        public ?int $orderId = null,
        public ?AddParcelRequest $order = null,
        public ?string $source = null,
        public ?int $sourceOrderId = null,
        public ?string $createdAt = null,
        public ?string $importedOrderCreatedAt = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $orderToSend = is_array($data['order_to_send'] ?? null) ? $data['order_to_send'] : $data;

        return new self(
            id: (int) ($orderToSend['id'] ?? 0),
            status: (string) ($orderToSend['status'] ?? ''),
            error: isset($orderToSend['error']) ? (string) $orderToSend['error'] : null,
            orderId: isset($orderToSend['order_id']) ? (int) $orderToSend['order_id'] : null,
            order: isset($orderToSend['order']) && is_array($orderToSend['order'])
                ? AddParcelRequest::fromArray($orderToSend['order'])
                : null,
            source: isset($orderToSend['source']) ? (string) $orderToSend['source'] : null,
            sourceOrderId: isset($orderToSend['source_order_id']) ? (int) $orderToSend['source_order_id'] : null,
            createdAt: isset($orderToSend['created_at']) ? (string) $orderToSend['created_at'] : null,
            importedOrderCreatedAt: isset($orderToSend['imported_order_created_at']) ? (string) $orderToSend['imported_order_created_at'] : null,
        );
    }
}
