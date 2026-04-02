<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs;

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
        return new self(
            cod: (bool) ($data['cod']['status'] ?? false),
            codAmount: isset($data['cod']['amount']) ? (float) $data['cod']['amount'] : null,
            openPackage: (bool) ($data['open_package']['status'] ?? false),
            rod: (bool) ($data['rod']['status'] ?? false),
            rodCode: (string) ($data['rod']['rod_code'] ?? null),
            rop: (bool) ($data['rop']['status'] ?? false),
            saturdayDelivery: (bool) ($data['saturday_delivery']['status'] ?? false),
            smsNotify: (bool) ($data['sms_notify']['status'] ?? false),
            swap: (bool) ($data['swap']['status'] ?? false),
            epod: (bool) ($data['epod']['status'] ?? false),
        );
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
