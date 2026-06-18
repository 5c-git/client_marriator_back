<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserExternalSync\UserExternalSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserExternalSyncController extends Controller
{
    public function __construct(private readonly UserExternalSyncService $syncService)
    {
    }

    /**
     * Receive user registration data from an external system and apply it
     * to the local user record.
     */
    public function sync(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'registration_fields' => 'required|array',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'errors' => $e->errors(),
            ], 422);
        }

        $user = User::query()->find($validated['user_id']);

        if ($user === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        $this->syncService->updateFromExternalSystem($user, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'User data synchronized',
            'user_id' => $user->id,
        ]);
    }
}
