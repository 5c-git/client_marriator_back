<?php

namespace App\Services\Local\Repositories\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Models\Order\Order;
use App\Models\Order\OrderActivities;
use App\Services\Local\Repositories\Contracts\OrderRepository;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class EloquentOrderRepository implements OrderRepository
{
    public function createOrder(CreateOrderRequest $orderRequest, int $userId): Order
    {
        $order = Order::create([
            'place_id' => $orderRequest->placeId,
            'user_id' => $userId,
            'self_employed' => $orderRequest->selfEmployed,
        ]);

        foreach ($orderRequest->viewActivities as $activity) {
            $orderActivity = new OrderActivities([
                'view_activity_id' => $activity['viewActivityId'],
                'count' => $activity['count'],
                'date_start' => $activity['dateStart'],
                'date_end' => $activity['dateEnd'],
                'need_foto' => $activity['needFoto'],
                'date_activity' => $this->processDateActivity($activity['dateActivity']),
            ]);

            $order->orderActivities()->save($orderActivity);
        }

        return $order;
    }

    private function processDateActivity(array $dateActivities): array
    {

        return array_map(function ($item) {
            return [
                'timeStart' => $item['timeStart'],
                'timeEnd' => $item['timeEnd'],
                'placeIds' => $item['placeIds'],
            ];
        }, $dateActivities);
    }

    public function getUserOrderByStatus(?OrderStatusEnum $status, int $userId, int $page = 1,int $perPage = 10): Paginator
    {
        return Order::query()->where('user_id',$userId)
            ->when($status, function ($q) use ($status) {
                return $q->where('status', $status->value);
            })->simplePaginate($perPage);;
    }

    public function cancelOrder(int $orderId): bool
    {
        return (bool)Order::query()
            ->where('id',$orderId)
            ->update(
                ['status'=>OrderStatusEnum::canceled->value]
            );
    }

    public function sendOrder(int $orderId): bool
    {
        return (bool)Order::query()
            ->where('id',$orderId)
            ->update(
                ['status'=>OrderStatusEnum::notAccepted->value]
            );
    }

    public function updateOrder(CreateOrderRequest $orderRequest): Order
    {
        $order = Order::where('id', $orderRequest->orderId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        DB::transaction(function () use ($order, $orderRequest) {
            $order->update([
                'place_id' => $orderRequest->placeId,
                'self_employed' => $orderRequest->selfEmployed,
            ]);

            $order->orderActivities()->delete();

            foreach ($orderRequest->viewActivities as $activity) {
                $order->orderActivities()->create([
                    'view_activity_id' => $activity['viewActivityId'],
                    'count' => $activity['count'],
                    'date_start' => $activity['dateStart'],
                    'date_end' => $activity['dateEnd'],
                    'need_foto' => $activity['needFoto'],
                    'date_activity' => $this->processDateActivity($activity['dateActivity']),
                ]);
            }
        });

        return $order;
    }
}
