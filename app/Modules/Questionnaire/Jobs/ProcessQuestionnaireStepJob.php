<?php

namespace Modules\Questionnaire\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Questionnaire\Exceptions\QuestionnaireProcessingException;
use Modules\Questionnaire\Models\Questionnaire;
use Modules\Questionnaire\Services\QuestionnaireProcessor;
use Modules\Questionnaire\Services\Steps\QuestionnaireStepInterface;

class ProcessQuestionnaireStepJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    public int $tries = 1;

    public function __construct(
        private readonly int $questionnaireId,
        private readonly int $stepIndex,
    ) {
        $this->onQueue(config('questionnaire.queue'));
    }

    public function questionnaireId(): int
    {
        return $this->questionnaireId;
    }

    public function stepIndex(): int
    {
        return $this->stepIndex;
    }

    public function handle(QuestionnaireProcessor $processor): void
    {
        $questionnaire = Questionnaire::query()->find($this->questionnaireId);

        if ($questionnaire === null) {
            Log::channel('single')->warning('ProcessQuestionnaireStepJob: questionnaire not found', [
                'questionnaire_id' => $this->questionnaireId,
            ]);

            return;
        }

        $steps = config('questionnaire.steps', []);

        if (! isset($steps[$this->stepIndex])) {
            $questionnaire->markCompleted();
            $processor->releaseLock($questionnaire);

            return;
        }

        $stepClass = $steps[$this->stepIndex];

        if (! is_a($stepClass, QuestionnaireStepInterface::class, true)) {
            $questionnaire->markFailed(
                $this->stepIndex,
                $stepClass,
                "Step class {$stepClass} does not implement QuestionnaireStepInterface."
            );
            $processor->releaseLock($questionnaire);

            return;
        }

        $questionnaire->markInProgress($this->stepIndex, $stepClass);

        /** @var QuestionnaireStepInterface $step */
        $step = app($stepClass);

        try {
            $step->handle($questionnaire);

            $questionnaire->appendLog([
                'step_index' => $this->stepIndex,
                'step_class' => $stepClass,
                'step_name' => $step->name(),
                'status' => 'success',
                'processed_at' => now()->toDateTimeString(),
            ]);
            $questionnaire->save();

            // Dispatch next step
            self::dispatch($questionnaire->id, $this->stepIndex + 1);
        } catch (QuestionnaireProcessingException $e) {
            $questionnaire->appendLog([
                'step_index' => $this->stepIndex,
                'step_class' => $stepClass,
                'step_name' => $step->name(),
                'status' => 'failed',
                'required' => $step->isRequired(),
                'message' => $e->getMessage(),
                'processed_at' => now()->toDateTimeString(),
            ]);

            if ($step->isRequired()) {
                $questionnaire->markFailed($this->stepIndex, $stepClass, $e->getMessage());
                $processor->releaseLock($questionnaire);

                return;
            }

            $questionnaire->save();
            self::dispatch($questionnaire->id, $this->stepIndex + 1);
        } catch (\Throwable $e) {
            Log::channel('single')->error('ProcessQuestionnaireStepJob: unexpected error', [
                'questionnaire_id' => $this->questionnaireId,
                'step_index' => $this->stepIndex,
                'step_class' => $stepClass,
                'exception' => $e->getMessage(),
            ]);

            $questionnaire->appendLog([
                'step_index' => $this->stepIndex,
                'step_class' => $stepClass,
                'step_name' => $step->name(),
                'status' => 'error',
                'message' => $e->getMessage(),
                'processed_at' => now()->toDateTimeString(),
            ]);
            $questionnaire->markFailed($this->stepIndex, $stepClass, $e->getMessage());
            $processor->releaseLock($questionnaire);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('single')->error('ProcessQuestionnaireStepJob: job failed', [
            'questionnaire_id' => $this->questionnaireId,
            'step_index' => $this->stepIndex,
            'exception' => $exception->getMessage(),
        ]);
    }
}
