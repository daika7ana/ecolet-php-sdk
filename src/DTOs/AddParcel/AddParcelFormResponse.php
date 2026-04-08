<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\AddParcel;

/**
 * Form validation response from reload-form endpoint.
 */
final readonly class AddParcelFormResponse
{
    /**
     * @param string[] $info Warnings/info about the order
     * @param array<string, string[]> $errors Validation errors keyed by field
     */
    public function __construct(
        public ServicePricingInfo $pricing,
        public int $billingWeight,
        public int $vat,
        public array $info = [],
        public array $errors = [],
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            pricing: ServicePricingInfo::fromArray($data),
            billingWeight: (int) ($data['billing_weight'] ?? 0),
            vat: (int) ($data['vat'] ?? 19),
            info: (array) ($data['info'] ?? []),
            errors: (array) ($data['errors'] ?? []),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = $this->pricing->toArray();
        $data['billing_weight'] = $this->billingWeight;
        $data['vat'] = $this->vat;
        $data['info'] = $this->info;
        $data['errors'] = $this->errors;

        return $data;
    }

    /**
     * Check if there are any validation errors.
     */
    public function hasErrors(): bool
    {
        return count($this->getErrorMessages()) > 0;
    }

    /**
     * Get all error messages as a flat array.
     *
     * @return string[]
     */
    public function getErrorMessages(): array
    {
        $messages = [];
        foreach ($this->errors as $fieldErrors) {
            if (is_array($fieldErrors)) {
                $messages = array_merge($messages, $fieldErrors);
            }
        }

        return $messages;
    }
}
