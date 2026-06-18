<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\UserExternalSync\UserExternalSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class SyncUserToExternalSystemJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 60;

    public int $tries = 3;

    public function __construct(private readonly int $userId)
    {
        $this->onQueue('external-sync');
    }

    public function userId(): int
    {
        return $this->userId;
    }

    /**
     * Dispatch a sync job for the given user, replacing any pending jobs
     * for the same user so that only the latest snapshot is sent.
     */
    public static function dispatchForUser(User $user): void
    {
        self::removePendingJobsForUser($user->id);
        self::dispatch($user->id);
    }

    /**
     * Remove pending sync jobs for a given user from the queue.
     */
    public static function removePendingJobsForUser(int $userId): void
    {
        $connection = config('queue.default');

        if ($connection === 'database') {
            self::removePendingDatabaseJobs($userId);
        } elseif ($connection === 'redis') {
            self::removePendingRedisJobs($userId);
        }
    }

    /**
     * Execute the job.
     */
    public function handle(UserExternalSyncService $service): void
    {
        return;
        $user = User::query()->find($this->userId);

        if ($user === null) {
            Log::channel('single')->warning('SyncUserToExternalSystemJob: user not found', [
                'user_id' => $this->userId,
            ]);

            return;
        }

        $service->sendToExternalSystem($user);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('single')->error('SyncUserToExternalSystemJob: failed', [
            'user_id' => $this->userId,
            'exception' => $exception->getMessage(),
        ]);
    }

    private static function removePendingDatabaseJobs(int $userId): void
    {
        $table = config('queue.connections.database.table', 'jobs');
        $class = self::class;
        $needle = self::serializedUserIdNeedle($userId);

        DB::table($table)
            ->where('queue', 'external-sync')
            ->where('payload', 'like', '%' . addcslashes($class, '\\') . '%')
            ->where('payload', 'like', '%' . addcslashes($needle, '\\%_') . '%')
            ->delete();
    }

    private static function removePendingRedisJobs(int $userId): void
    {
        $queue = 'external-sync';
        $redis = Redis::connection(config('queue.connections.redis.connection', 'default'));
        $prefix = config('database.redis.options.prefix', '');
        $queueKey = $prefix . 'queues:' . $queue;

        $class = self::class;
        $needle = self::serializedUserIdNeedle($userId);

        $jobs = $redis->lrange($queueKey, 0, -1);

        foreach ($jobs as $job) {
            if (str_contains($job, $class) && str_contains($job, $needle)) {
                $redis->lrem($queueKey, 0, $job);
            }
        }
    }

    /**
     * Build a string fragment that appears in serialized job payload
     * for the given userId.
     */
    private static function serializedUserIdNeedle(int $userId): string
    {
        return 's:6:"userId";i:' . $userId . ';';
    }
}
