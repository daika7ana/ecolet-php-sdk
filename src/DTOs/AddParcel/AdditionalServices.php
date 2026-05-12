<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\AddParcel;

/**
 * Additional services for an order.
 */
final readonly class AdditionalServices
{
    public function __construct(
        public bool $cod = false,
        public ?float $codAmount = null,
        public bool $openPackage = false,
        public bool $rod = false,
        public ?string $rodCode = null,
        public bool $rop = false,
        public bool $saturdayDelivery = false,
        public bool $smsNotify = false,
        public bool $swap = false,
        public bool $epod = false,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $cod = self::arrayOrEmpty($data['cod'] ?? null);
        $openPackage = self::arrayOrEmpty($data['open_package'] ?? null);
        $rod = self::arrayOrEmpty($data['rod'] ?? null);
        $rop = self::arrayOrEmpty($data['rop'] ?? null);
        $saturdayDelivery = self::arrayOrEmpty($data['saturday_delivery'] ?? null);
        $smsNotify = self::arrayOrEmpty($data['sms_notify'] ?? null);
        $swap = self::arrayOrEmpty($data['swap'] ?? null);
        $epod = self::arrayOrEmpty($data['epod'] ?? null);

        return new self(
            cod: (bool) ($cod['status'] ?? false),
            codAmount: isset($cod['amount']) ? (float) $cod['amount'] : null,
            openPackage: (bool) ($openPackage['status'] ?? false),
            rod: (bool) ($rod['status'] ?? false),
            rodCode: isset($rod['rod_code']) ? (string) $rod['rod_code'] : null,
            rop: (bool) ($rop['status'] ?? false),
            saturdayDelivery: (bool) ($saturdayDelivery['status'] ?? false),
            smsNotify: (bool) ($smsNotify['status'] ?? false),
            swap: (bool) ($swap['status'] ?? false),
            epod: (bool) ($epod['status'] ?? false),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function arrayOrEmpty(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $services = [
            'cod' => [
                'status' => $this->cod,
            ],
            'open_package' => [
                'status' => $this->openPackage,
            ],
            'rod' => [
                'status' => $this->rod,
            ],
            'rop' => [
                'status' => $this->rop,
            ],
            'saturday_delivery' => [
                'status' => $this->saturdayDelivery,
            ],
            'sms_notify' => [
                'status' => $this->smsNotify,
            ],
            'swap' => [
                'status' => $this->swap,
            ],
            'epod' => [
                'status' => $this->epod,
            ],
        ];

        if ($this->codAmount !== null) {
            $services['cod']['amount'] = $this->codAmount;
        }

        if ($this->rodCode !== null) {
            $services['rod']['rod_code'] = $this->rodCode;
        }

        return $services;
    }
}
