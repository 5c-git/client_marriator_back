<?php

namespace App\Services\Local\Repositories\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\Order\ConvertTaskRequest;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Models\Order\Order;
use App\Models\Order\OrderActivities;
use App\Models\User;
use App\Services\Local\Repositories\Contracts\OrderRepository;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use App\Models\Order\Task;
use App\Models\Order\TaskActivity;

class EloquentOrderRepository implements OrderRepository
{
    public function createOrder(CreateOrderRequest $orderRequest, int $userId): Order
    {
        $order = Order::create([
            'place_id' => $orderRequest->placeId,
            'user_id' => $userId,
            'self_employed' => $orderRequest->selfEmployed,
            'status' => OrderStatusEnum::new->value
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
            })->simplePaginate($perPage);
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

    public function getOrderByUserSyncData(User $user,?OrderStatusEnum $status, int $page = 1, int $perPage = 10): Paginator
    {
        $place = $user->place?->pluck('id')->toArray();

        return Order::query()
            ->where(function ($query) use ($status, $place) {
                $query->when($status, fn($q) => $q->where('status', $status->value))
                    ->when($place, fn($q) => $q->whereIn('place_id', $place))
                ->where('status','!=',OrderStatusEnum::accepted->value);
            })
            ->orWhere(function ($query) use ($user,$status) {
                $userIdsSupervisor = $user->supervisors?->pluck('id')->toArray();
                $userIdsSupervisor[] = $user->id;
                $query = $query->whereHas('acceptingUsers', fn($q) => $q->whereIn('users.id', $userIdsSupervisor));
                if($status == OrderStatusEnum::accepted) {
                    $query->where('status', OrderStatusEnum::accepted->value);
                }
            })
            ->simplePaginate($perPage);
    }

    public function acceptedOrder(User $user, int $orderId): bool
    {
        return (bool)Order::query()
            ->where('id',$orderId)
            ->update(
                ['status'=>OrderStatusEnum::accepted]
            );
    }

    public function convertTask(User $user, ConvertTaskRequest $request): Task
    {
        $order = Order::where('id',$request->orderId)->first();
        $countTaskForUser = $order->tasks?->where('user_id',$user->id)->count();
        if($countTaskForUser == 0) {
            $task = new Task([
                'place_id' => $order->place_id,
                'user_id' => $order->user_id,
                'self_employed' => $order->self_employed,
                'status' => OrderStatusEnum::accepted,
                'order_id' => $order->id,
                'price' => 0,
                'income' => 0,
                'scope_of_services' => 0,
                'accept_user_id' => $request->supervisorId ?? $user->id,
                'specialist_user_id' => $user->id
            ]);

            $task->save();

            foreach ($order->orderActivities as $orderActivity) {
                $taskActivity = new TaskActivity([
                    'view_activity_id' => $orderActivity->view_activity_id,
                    'count' => $orderActivity->count,
                    'date_start' => $orderActivity->date_start,
                    'date_end' => $orderActivity->date_end,
                    'need_foto' => $orderActivity->need_foto,
                    'date_activity' => $orderActivity->date_activity,
                    'task_id' => $task->id
                ]);

                $task->taskActivities()->save($taskActivity);
            }
        }else{
            $task = $order->tasks?->where('user_id',$user->id)->first();
        }

        return $task;
    }

    public function getTaskByUserSyncData(User $user, ?OrderStatusEnum $status, int $page = 1, int $perPage = 10): Paginator
    {
        return Task::query()
            ->where(function ($query) use ($status) {
                $query->when($status, fn($q) => $q->where('status', $status->value))
                    ->where('status','!=',OrderStatusEnum::accepted->value);
            })
            ->orWhere(function ($query) use ($user,$status) {
                $userIdsSupervisor = $user->supervisors?->pluck('id')->toArray();
                $userIdsSupervisor[] = $user->id;
                $query = $query->whereIn('accept_user_id', $userIdsSupervisor);
                if($status == OrderStatusEnum::accepted) {
                    $query->where('status', OrderStatusEnum::accepted->value);
                }
            })
            ->simplePaginate($perPage);
    }
}
