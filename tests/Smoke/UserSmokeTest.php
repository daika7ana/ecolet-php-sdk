<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Smoke;

use Daika7ana\Ecolet\DTOs\Users\User;
use Daika7ana\Ecolet\Tests\Smoke\Concerns\InteractsWithAuthenticatedSmokeClient;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

final class UserSmokeTest extends TestCase
{
    use InteractsWithAuthenticatedSmokeClient;

    #[Group('smoke')]
    public function testGetMeReturnsAuthenticatedUser(): void
    {
        $client = $this->makeAuthenticatedClient('user');

        $user = $client->users()->getMe();

        $this->assertInstanceOf(User::class, $user);
        $this->assertGreaterThan(0, $user->id);
        $this->assertNotSame('', $user->email);
        $this->assertNotSame('', $user->name);
    }

    #[Group('smoke')]
    public function testGetMeEmailMatchesCredentials(): void
    {
        $username = $this->smokeCredentials('user')['username'];
        $client = $this->makeAuthenticatedClient('user');

        $user = $client->users()->getMe();

        $this->assertSame($username, $user->email);
    }
}
