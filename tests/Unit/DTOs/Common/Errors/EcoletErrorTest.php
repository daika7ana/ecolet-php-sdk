<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\DTOs\Common\Errors;

use Daika7ana\Ecolet\DTOs\Common\Errors\EcoletError;
use PHPUnit\Framework\TestCase;

class EcoletErrorTest extends TestCase
{
    public function testFromArrayMapsEcoletFields(): void
    {
        $error = EcoletError::fromArray([
            'code' => 500,
            'message' => 'Server error',
            'id' => 'err-1',
            'context' => 'orders',
            'component' => 'api',
        ]);

        $this->assertSame(500, $error->code);
        $this->assertSame('Server error', $error->message);
        $this->assertSame('err-1', $error->id);
        $this->assertSame('orders', $error->context);
        $this->assertSame('api', $error->component);
        $this->assertSame('Server error', $error->raw['message']);
    }

    public function testFromArrayFallsBackToEcoletErrorKeysAndCode(): void
    {
        $error = EcoletError::fromArray(['general_error' => 'Service unavailable.'], 503);

        $this->assertSame(503, $error->code);
        $this->assertSame('Service unavailable.', $error->message);
        $this->assertSame('', $error->id);

        $this->assertSame('Bad request.', EcoletError::fromArray(['error' => 'Bad request.'])->message);
        $this->assertSame('Ecolet API error.', EcoletError::fromArray([])->message);
    }

    public function testValidationRelatedDetectsCodeAndFieldErrors(): void
    {
        $this->assertTrue(EcoletError::fromArray([], 422)->isValidationRelated());
        $this->assertTrue(EcoletError::fromArray(['errors' => ['parcels' => ['Required']]])->isValidationRelated());
        $this->assertFalse(EcoletError::fromArray(['errors' => []])->isValidationRelated());
        $this->assertFalse(EcoletError::fromArray(['message' => 'Server error'])->isValidationRelated());
    }

    public function testAuthenticationRelatedDetectsCodesAndMessages(): void
    {
        $this->assertTrue(EcoletError::fromArray([], 401)->isAuthenticationRelated());
        $this->assertTrue(EcoletError::fromArray([], 403)->isAuthenticationRelated());
        $this->assertTrue(EcoletError::fromArray(['message' => 'Invalid credentials'])->isAuthenticationRelated());
        $this->assertTrue(EcoletError::fromArray(['message' => 'Unauthenticated'])->isAuthenticationRelated());
        $this->assertFalse(EcoletError::fromArray(['message' => 'Server error'])->isAuthenticationRelated());
    }
}
