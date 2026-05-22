<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Login helper - returns token
     */
    protected function loginAs(array $userOverrides = []): string
    {
        $user = \App\Models\User::factory()->create($userOverrides);
        return $user->createToken('test-token')->plainTextToken;
    }

    /**
     * Login as admin helper
     */
    protected function loginAsAdmin(): string
    {
        return $this->loginAs(['role' => 'admin']);
    }

    /**
     * Login as trainer helper
     */
    protected function loginAsTrainer(): string
    {
        return $this->loginAs(['role' => 'trainer']);
    }
}
