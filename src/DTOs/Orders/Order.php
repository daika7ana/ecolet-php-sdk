<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Orders;

final readonly class Order
{
    /**
     * @param list<OrderFee> $fees
     * @param list<OrderStatus> $statuses
     */
    public function __construct(
        public int $id,
        public string $number,
        public string $awb,
        public ?OrderService $service = null,
        public ?string $shipmentType = null,
        public ?string $primaryOrderAwb = null,
        public ?OrderAddress $sender = null,
        public ?OrderAddress $receiver = null,
        public ?string $waybillExtension = null,
        public bool $waybillHasBeenDownloaded = false,
        public ?string $status = null,
        public ?string $type = null,
        public int $amount = 0,
        public float $price = 0.0,
        public string $content = '',
        public ?string $shape = null,
        public int $weight = 0,
        public int $length = 0,
        public int $width = 0,
        public int $height = 0,
        public ?float $declaredValue = null,
        public ?float $cod = null,
        public ?string $codReceivedAt = null,
        public ?string $codReturnedAt = null,
        public ?string $observations = null,
        public ?string $pickupDate = null,
        public ?string $pickupHour = null,
        public array $fees = [],
        public int $vat = 0,
        public array $statuses = [],
        public ?string $updatedAt = null,
        public ?string $createdAt = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $order = is_array($data['data'] ?? null) ? $data['data'] : $data;

        $fees = [];
        foreach ($order['fees'] ?? [] as $fee) {
            if (!is_array($fee)) {
                continue;
            }

            $fees[] = OrderFee::fromArray($fee);
        }

        $statuses = [];
        foreach ($order['statuses'] ?? [] as $status) {
            if (!is_array($status)) {
                continue;
            }

            $statuses[] = OrderStatus::fromArray($status);
        }

        $awb = (string) ($order['awb'] ?? $order['number'] ?? '');

        return new self(
            id: (int) ($order['id'] ?? 0),
            number: $awb,
            awb: $awb,
            service: is_array($order['service'] ?? null) ? OrderService::fromArray($order['service']) : null,
            shipmentType: isset($order['shipment_type']) ? (string) $order['shipment_type'] : null,
            primaryOrderAwb: isset($order['primary_order_awb']) ? (string) $order['primary_order_awb'] : null,
            sender: is_array($order['sender'] ?? null) ? OrderAddress::fromArray($order['sender']) : null,
            receiver: is_array($order['receiver'] ?? null) ? OrderAddress::fromArray($order['receiver']) : null,
            waybillExtension: isset($order['waybill_extension']) ? (string) $order['waybill_extension'] : null,
            waybillHasBeenDownloaded: (bool) ($order['waybill_has_been_downloaded'] ?? false),
            status: isset($order['status']) ? (string) $order['status'] : null,
            type: isset($order['type']) ? (string) $order['type'] : null,
            amount: (int) ($order['amount'] ?? 0),
            price: (float) ($order['price'] ?? 0.0),
            content: (string) ($order['content'] ?? ''),
            shape: isset($order['shape']) ? (string) $order['shape'] : null,
            weight: (int) ($order['weight'] ?? 0),
            length: (int) ($order['length'] ?? 0),
            width: (int) ($order['width'] ?? 0),
            height: (int) ($order['height'] ?? 0),
            declaredValue: isset($order['declared_value']) ? (float) $order['declared_value'] : null,
            cod: isset($order['cod']) ? (float) $order['cod'] : null,
            codReceivedAt: isset($order['cod_received_at']) ? (string) $order['cod_received_at'] : null,
            codReturnedAt: isset($order['cod_returned_at']) ? (string) $order['cod_returned_at'] : null,
            observations: isset($order['observations']) ? (string) $order['observations'] : null,
            pickupDate: isset($order['pickup_date']) ? (string) $order['pickup_date'] : null,
            pickupHour: isset($order['pickup_hour']) ? (string) $order['pickup_hour'] : null,
            fees: $fees,
            vat: (int) ($order['vat'] ?? 0),
            statuses: $statuses,
            updatedAt: isset($order['updated_at']) ? (string) $order['updated_at'] : null,
            createdAt: isset($order['created_at']) ? (string) $order['created_at'] : null,
        );
    }
}
