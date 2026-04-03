<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\AddParcel;

/**
 * Optional coupon/promotion code.
 */
final readonly class CouponInfo
{
    public function __construct(
        public string $code,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            code: $data['code'],
        );
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
        ];
    }
}
