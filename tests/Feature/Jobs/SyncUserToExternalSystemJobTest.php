<?php

namespace Tests\Feature\Jobs;

use App\Jobs\SyncUserToExternalSystemJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SyncUserToExternalSystemJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_is_dispatched_for_user(): void
    {
        Bus::fake([SyncUserToExternalSystemJob::class]);

        $user = User::factory()->create();

        SyncUserToExternalSystemJob::dispatchForUser($user);

        Bus::assertDispatched(SyncUserToExternalSystemJob::class, function ($job) use ($user) {
            return $job->userId() === $user->id;
        });
    }

    public function test_job_uses_external_sync_queue(): void
    {
        Bus::fake([SyncUserToExternalSystemJob::class]);

        $user = User::factory()->create();

        SyncUserToExternalSystemJob::dispatchForUser($user);

        Bus::assertDispatched(SyncUserToExternalSystemJob::class, function ($job) {
            return $job->queue === 'external-sync';
        });
    }
}
