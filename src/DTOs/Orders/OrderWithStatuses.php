<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Orders;

final readonly class OrderWithStatuses
{
    /**
     * @param list<OrderStatus> $statuses
     */
    public function __construct(
        public int $id,
        public string $awb,
        public string $courier,
        public string $status,
        public array $statuses,
        public string $updatedAt,
        public string $createdAt,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $statuses = [];

        foreach ($data['statuses'] ?? [] as $status) {
            if (!is_array($status)) {
                continue;
            }

            $statuses[] = OrderStatus::fromArray($status);
        }

        return new self(
            id: (int) ($data['id'] ?? 0),
            awb: (string) ($data['awb'] ?? ''),
            courier: (string) ($data['courier'] ?? ''),
            status: (string) ($data['status'] ?? ''),
            statuses: $statuses,
            updatedAt: (string) ($data['updated_at'] ?? ''),
            createdAt: (string) ($data['created_at'] ?? ''),
        );
    }
}
