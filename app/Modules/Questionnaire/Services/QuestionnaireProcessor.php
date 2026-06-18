<?php

namespace Modules\Questionnaire\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Modules\Questionnaire\Jobs\ProcessQuestionnaireStepJob;
use Modules\Questionnaire\Models\Questionnaire;

class QuestionnaireProcessor
{
    public function __construct(private readonly QuestionnaireBuilder $builder)
    {
    }

    /**
     * Build or rebuild questionnaire for user and start processing from step 0.
     */
    public function processUser(\App\Models\User $user): Questionnaire
    {
        $questionnaire = $this->builder->buildForUser($user);

        $this->cancelPendingJobs($questionnaire);
        $this->acquireLock($questionnaire);

        ProcessQuestionnaireStepJob::dispatch($questionnaire->id, 0);

        return $questionnaire;
    }

    /**
     * Restart processing of an existing questionnaire from the beginning.
     */
    public function restart(Questionnaire $questionnaire): Questionnaire
    {
        $questionnaire->status = \Modules\Questionnaire\Enums\QuestionnaireStatus::PENDING->value;
        $questionnaire->current_step_index = null;
        $questionnaire->current_step_class = null;
        $questionnaire->error_message = null;
        $questionnaire->completed_at = null;
        $questionnaire->failed_at = null;
        $questionnaire->logs = [];
        $questionnaire->save();

        $this->cancelPendingJobs($questionnaire);
        $this->acquireLock($questionnaire);

        ProcessQuestionnaireStepJob::dispatch($questionnaire->id, 0);

        return $questionnaire;
    }

    /**
     * Remove pending step jobs for the questionnaire from the queue.
     */
    public function cancelPendingJobs(Questionnaire $questionnaire): void
    {
        $connection = config('queue.default');
        $queue = config('questionnaire.queue');

        if ($connection === 'database') {
            $this->cancelDatabaseJobs($questionnaire, $queue);
        } elseif ($connection === 'redis') {
            $this->cancelRedisJobs($questionnaire, $queue);
        }
    }

    /**
     * Acquire a Redis lock so that only one worker processes a questionnaire
     * at a time. Lock is released when the job finishes.
     */
    public function acquireLock(Questionnaire $questionnaire): bool
    {
        $key = $this->lockKey($questionnaire);
        $redis = Redis::connection();

        // NX = only if not exists, EX = expire in 60 seconds
        return (bool) $redis->set($key, 'locked', 'NX', 'EX', 60);
    }

    public function releaseLock(Questionnaire $questionnaire): void
    {
        Redis::connection()->del($this->lockKey($questionnaire));
    }

    public function lockKey(Questionnaire $questionnaire): string
    {
        $prefix = config('database.redis.options.prefix', '');

        return $prefix.'questionnaire:lock:'.$questionnaire->id;
    }

    private function cancelDatabaseJobs(Questionnaire $questionnaire, string $queue): void
    {
        $table = config('queue.connections.database.table', 'jobs');
        $class = ProcessQuestionnaireStepJob::class;
        $needle = 's:15:"questionnaireId";i:'.$questionnaire->id.';';

        DB::table($table)
            ->where('queue', $queue)
            ->where('payload', 'like', '%'.addcslashes($class, '\\').'%')
            ->where('payload', 'like', '%'.addcslashes($needle, '\\%_').'%')
            ->delete();
    }

    private function cancelRedisJobs(Questionnaire $questionnaire, string $queue): void
    {
        $redis = Redis::connection(config('queue.connections.redis.connection', 'default'));
        $prefix = config('database.redis.options.prefix', '');
        $queueKey = $prefix.'queues:'.$queue;

        $class = ProcessQuestionnaireStepJob::class;
        $needle = 's:15:"questionnaireId";i:'.$questionnaire->id.';';

        $jobs = $redis->lrange($queueKey, 0, -1);

        foreach ($jobs as $job) {
            if (str_contains($job, $class) && str_contains($job, $needle)) {
                $redis->lrem($queueKey, 0, $job);
            }
        }
    }
}
