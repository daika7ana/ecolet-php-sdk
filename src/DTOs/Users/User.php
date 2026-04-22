<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Users;

final readonly class User
{
    /**
     * @param list<string> $forbiddenCouriers
     * @param list<string> $forbiddenServices
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public bool $isBanned = false,
        public bool $verified = false,
        public ?string $accountType = null,
        public ?string $taxId = null,
        public ?string $phone = null,
        public string $country = 'ro',
        public float $balance = 0.0,
        public float $creditUsage = 0.0,
        public float $creditLimit = 0.0,
        public ?string $printerType = null,
        public ?string $printerFormat = null,
        public ?int $invoiceAddressId = null,
        public ?int $defaultAddressId = null,
        public ?int $bankAccountId = null,
        public bool $agreementMarketing = false,
        public int $newInvoicesCount = 0,
        public int $notPaidInvoices = 0,
        public int $newContactFormMessages = 0,
        public int $newOrdersToSendCount = 0,
        public ?string $dangerBanner = null,
        public bool $blockShipment = false,
        public array $forbiddenCouriers = [],
        public array $forbiddenServices = [],
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $forbiddenCouriers = array_values(array_filter(
            array_map(static fn(mixed $item): ?string => is_scalar($item) ? (string) $item : null, $data['forbidden_couriers'] ?? []),
            static fn(?string $item): bool => $item !== null,
        ));

        $forbiddenServices = array_values(array_filter(
            array_map(static fn(mixed $item): ?string => is_scalar($item) ? (string) $item : null, $data['forbidden_services'] ?? []),
            static fn(?string $item): bool => $item !== null,
        ));

        return new self(
            id: (int) ($data['id'] ?? 0),
            name: (string) ($data['name'] ?? ''),
            email: (string) ($data['email'] ?? ''),
            isBanned: (bool) ($data['is_banned'] ?? false),
            verified: (bool) ($data['verified'] ?? false),
            accountType: isset($data['account_type']) ? (string) $data['account_type'] : null,
            taxId: isset($data['tax_id']) ? (string) $data['tax_id'] : null,
            phone: isset($data['phone']) ? (string) $data['phone'] : null,
            country: (string) ($data['country'] ?? 'ro'),
            balance: (float) ($data['balance'] ?? 0.0),
            creditUsage: (float) ($data['credit_usage'] ?? 0.0),
            creditLimit: (float) ($data['credit_limit'] ?? 0.0),
            printerType: isset($data['printer_type']) ? (string) $data['printer_type'] : null,
            printerFormat: isset($data['printer_format']) ? (string) $data['printer_format'] : null,
            invoiceAddressId: isset($data['invoice_address_id']) ? (int) $data['invoice_address_id'] : null,
            defaultAddressId: isset($data['default_address_id']) ? (int) $data['default_address_id'] : null,
            bankAccountId: isset($data['bank_account_id']) ? (int) $data['bank_account_id'] : null,
            agreementMarketing: (bool) ($data['agreement_marketing'] ?? false),
            newInvoicesCount: (int) ($data['new_invoices_count'] ?? 0),
            notPaidInvoices: (int) ($data['not_paid_invoices'] ?? 0),
            newContactFormMessages: (int) ($data['new_contact_form_messages'] ?? 0),
            newOrdersToSendCount: (int) ($data['new_orders_to_send_count'] ?? 0),
            dangerBanner: isset($data['danger_banner']) ? (string) $data['danger_banner'] : null,
            blockShipment: (bool) ($data['block_shipment'] ?? false),
            forbiddenCouriers: $forbiddenCouriers,
            forbiddenServices: $forbiddenServices,
        );
    }
}
