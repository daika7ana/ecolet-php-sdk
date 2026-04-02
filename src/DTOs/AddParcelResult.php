<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs;

/**
 * Tightly-typed response from add-parcel operations.
 *
 * Different operations return different response structures:
 * - reload-form: Returns form validation with pricing and service availability
 * - send-order: Returns order_to_send_id (integer)
 * - save-order-to-send: Returns order_to_send_id (integer)
 */
final readonly class AddParcelResult
{
    /**
     * @param array<string, mixed> $rawData Raw API response data
     * @param AddParcelFormResponse|null $formResponse Parsed form response (for reload-form)
     * @param int|null $orderToSendId Order ID (for send-order and save-order-to-send)
     */
    public function __construct(
        public array $rawData,
        public ?AddParcelFormResponse $formResponse = null,
        public ?int $orderToSendId = null,
    ) {}

    /**
     * Create from raw API response array.
     *
     * Automatically detects the operation type and structures the response accordingly.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $formResponse = null;
        $orderToSendId = null;

        // Detect response type: reload-form has 'form', send/save have 'order_to_send_id'
        if (isset($data['form'])) {
            $formResponse = AddParcelFormResponse::fromArray($data['form']);
        } elseif (isset($data['order_to_send_id'])) {
            $orderToSendId = (int) $data['order_to_send_id'];
        }

        return new self(
            rawData: $data,
            formResponse: $formResponse,
            orderToSendId: $orderToSendId,
        );
    }

    /**
     * Get the raw response data (for backward compatibility or accessing unmapped fields).
     *
     * @return array<string, mixed>
     */
    public function getRawData(): array
    {
        return $this->rawData;
    }

    /**
     * Check if this is a reload-form response.
     */
    public function isFormResponse(): bool
    {
        return $this->formResponse !== null;
    }

    /**
     * Check if this is a send/save response.
     */
    public function isOrderResponse(): bool
    {
        return $this->orderToSendId !== null;
    }
}
