<?php

namespace Tests\Unit;

use App\Jobs\SendWelcomeEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Queue;

class SendWelcomeEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_email_job_is_dispatchable(): void
    {
        $user = User::factory()->create();

        $job = new SendWelcomeEmail($user);

        $this->assertEquals($user->id, $job->user->id);
    }

    public function test_welcome_email_job_uses_emails_queue(): void
    {
        $user = User::factory()->create();
        $job = new SendWelcomeEmail($user);

        // Queue kontrolü
        $this->assertEquals('emails', $job->queue);
    }

    public function test_welcome_email_job_has_correct_retry_count(): void
    {
        $user = User::factory()->create();
        $job = new SendWelcomeEmail($user);

        $this->assertEquals(3, $job->tries);
    }

    public function test_welcome_email_job_has_timeout(): void
    {
        $user = User::factory()->create();
        $job = new SendWelcomeEmail($user);

        $this->assertEquals(30, $job->timeout);
    }

    public function test_welcome_email_job_dispatches_with_delay(): void
    {
        $user = User::factory()->create();

        Queue::fake();

        SendWelcomeEmail::dispatch($user)->delay(now()->addMinutes(1));

        Queue::assertPushed(SendWelcomeEmail::class, function ($job) use ($user) {
            return $job->user->id === $user->id;
        });
    }
}
