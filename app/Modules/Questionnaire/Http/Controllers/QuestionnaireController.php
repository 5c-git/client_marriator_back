<?php

namespace Modules\Questionnaire\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Modules\Questionnaire\Models\Questionnaire;
use Modules\Questionnaire\Services\QuestionnaireProcessor;

class QuestionnaireController extends Controller
{
    public function __construct(private readonly QuestionnaireProcessor $processor)
    {
    }

    /**
     * Start or restart questionnaire processing for a user.
     */
    public function start(User $user): JsonResponse
    {
        $questionnaire = $this->processor->processUser($user);

        return response()->json([
            'status' => 'success',
            'questionnaire_id' => $questionnaire->id,
            'user_id' => $questionnaire->user_id,
            'status_name' => $questionnaire->status,
        ]);
    }

    /**
     * Get current questionnaire status.
     */
    public function status(User $user): JsonResponse
    {
        $questionnaire = Questionnaire::query()->where('user_id', $user->id)->first();

        if ($questionnaire === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Questionnaire not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'questionnaire_id' => $questionnaire->id,
            'user_id' => $questionnaire->user_id,
            'status_name' => $questionnaire->status,
            'current_step_index' => $questionnaire->current_step_index,
            'current_step_class' => $questionnaire->current_step_class,
            'error_message' => $questionnaire->error_message,
            'updated_at' => $questionnaire->updated_at,
        ]);
    }

    /**
     * Get questionnaire data result.
     */
    public function result(User $user): JsonResponse
    {
        $questionnaire = Questionnaire::query()->where('user_id', $user->id)->first();

        if ($questionnaire === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Questionnaire not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'questionnaire_id' => $questionnaire->id,
            'user_id' => $questionnaire->user_id,
            'status_name' => $questionnaire->status,
            'data' => $questionnaire->data,
            'logs' => $questionnaire->logs,
        ]);
    }
}
