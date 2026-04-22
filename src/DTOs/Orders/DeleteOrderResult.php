<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Orders;

final readonly class DeleteOrderResult
{
    /**
     * @param list<string> $messages
     * @param array<string, mixed> $rawData
     */
    public function __construct(
        public array $messages,
        public array $rawData,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $messages = [];

        foreach ($data['data'] ?? [] as $item) {
            if (is_string($item)) {
                $messages[] = $item;

                continue;
            }

            if (!is_array($item)) {
                continue;
            }

            if (isset($item['description']) && is_string($item['description'])) {
                $messages[] = $item['description'];

                continue;
            }

            if (isset($item['message']) && is_string($item['message'])) {
                $messages[] = $item['message'];
            }
        }

        return new self(
            messages: $messages,
            rawData: $data,
        );
    }
}
