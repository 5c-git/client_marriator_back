<?php

namespace Modules\YandexSmena\Http\Controllers;

use App\Models\Order\OrderActivities;
use App\Models\Order\TaskActivity;
use Illuminate\Http\JsonResponse;
use Modules\YandexSmena\Exceptions\YandexSmenaConfigurationException;
use Modules\YandexSmena\Http\Requests\PublishYandexSmenaShiftRequest;
use Modules\YandexSmena\Services\SmenaShiftFactory;

class PublishYandexSmenaShiftController
{
    public function __construct(private readonly SmenaShiftFactory $factory)
    {
    }

    public function __invoke(PublishYandexSmenaShiftRequest $request): JsonResponse
    {
        $activity = $this->resolveActivity($request);

        try {
            $shifts = $this->factory->fromActivity($activity);
        } catch (YandexSmenaConfigurationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'shift_ids' => array_map(fn ($shift) => $shift->id, $shifts),
                'count' => count($shifts),
            ],
        ]);
    }

    private function resolveActivity(PublishYandexSmenaShiftRequest $request): OrderActivities|TaskActivity
    {
        if ($request->has('orderId')) {
            return OrderActivities::where('id', $request->input('orderActivityId'))
                ->where('order_id', $request->input('orderId'))
                ->firstOrFail();
        }

        return TaskActivity::where('id', $request->input('taskActivityId'))
            ->where('task_id', $request->input('taskId'))
            ->firstOrFail();
    }
}
