<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\Common\Errors;

final readonly class EcoletError
{
    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public int $code,
        public string $message,
        public string $id,
        public array $raw,
        public ?string $context = null,
        public ?string $component = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data, int $fallbackCode = 0): self
    {
        $message = $data['message'] ?? $data['error'] ?? $data['general_error'] ?? 'Ecolet API error.';

        return new self(
            code: (int) ($data['code'] ?? $fallbackCode),
            message: is_string($message) ? $message : 'Ecolet API error.',
            id: (string) ($data['id'] ?? $data['error_id'] ?? ''),
            raw: $data,
            context: isset($data['context']) ? (string) $data['context'] : null,
            component: isset($data['component']) ? (string) $data['component'] : null,
        );
    }

    public function isValidationRelated(): bool
    {
        // 400 also raises ValidationException; only 422 or payload field errors qualify here.
        if ($this->code === 422) {
            return true;
        }

        return isset($this->raw['errors']) && is_array($this->raw['errors']) && $this->raw['errors'] !== [];
    }

    public function isAuthenticationRelated(): bool
    {
        if ($this->code === 401 || $this->code === 403) {
            return true;
        }

        $message = strtolower($this->message);

        return str_contains($message, 'auth')
            || str_contains($message, 'password')
            || str_contains($message, 'credential')
            || str_contains($message, 'token')
            || str_contains($message, 'unauthenticated')
            || str_contains($message, 'logged user')
            || str_contains($message, 'access rights');
    }
}
