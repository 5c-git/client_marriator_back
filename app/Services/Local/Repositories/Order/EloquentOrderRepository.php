<?php

namespace App\Services\Local\Repositories\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\Order\BidDataRequest;
use App\Http\Requests\Order\ConvertTaskRequest;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\DeleteOrderActivityRequest;
use App\Models\Order\Bid;
use App\Models\Order\Order;
use App\Models\Order\OrderActivities;
use App\Models\User;
use App\Services\Local\Repositories\Contracts\OrderRepository;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order\Task;
use App\Models\Order\TaskActivity;
use App\Http\Requests\Order\CreateTaskRequest;
use App\Services\CoordinatesService;
use App\Models\Fields\Fields;
use App\Models\Fields\Directory\ViewActivities;
use App\Models\Fields\Directory\TaxStatus;
use App\Http\Requests\Order\CreateOrderActivityRequest;
use App\Http\Requests\Order\UpdateOrderRequest;

class EloquentOrderRepository implements OrderRepository
{
    public function createOrder(CreateOrderRequest $orderRequest, int $userId): Order
    {
        $order = Order::create([
            'place_id' => $orderRequest->placeId,
            'user_id' => $userId,
            'self_employed' => false,
            'status' => OrderStatusEnum::new->value
        ]);

        return $order;
    }

    public function createOrderActivity(CreateOrderActivityRequest $request): Order
    {
        $orderActivity = new OrderActivities([
            'view_activity_id' => $request->viewActivityId,
            'count' => $request->count,
            'date_start' => $request->dateStart,
            'date_end' => $request->dateEnd,
            'need_foto' => $request->needFoto,
            'date_activity' => $this->processDateActivity($request->dateActivity),
            'order_id' => $request->orderId
        ]);
        $orderActivity->save();

        return Order::where('id',$request->orderId)->first();
    }

    public function updateOrder(UpdateOrderRequest $orderRequest): Order
    {
        $order = Order::where('id', $orderRequest->orderId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        DB::transaction(function () use ($order, $orderRequest) {
            $order->update([
                'place_id' => $orderRequest->placeId??$order->placeId,
                'self_employed' => $orderRequest->selfEmployed??$order->selfEmployed,
            ]);
        });

        if($orderRequest->placeId){

        }

        return $order->fresh();
    }

    public function deleteOrderActivity(DeleteOrderActivityRequest $orderRequest): Order
    {
        OrderActivities::where('id',$orderRequest->orderActivityId)->where('order_id',$orderRequest->orderId)->delete();
        return Order::where('id',$orderRequest->orderId)->first();
    }
    public function createTask(CreateTaskRequest $taskRequest, int $userId): Task
    {
        $task = Task::create([
            'place_id' => $taskRequest->placeId,
            'user_id' => $userId,
            'self_employed' => $taskRequest->selfEmployed,
            'status' => OrderStatusEnum::new->value,
            'specialist_user_id' => null,
            'accept_user_id' => null,
            'order_id' => null,
            'price' => $taskRequest->price,
            'income' => $taskRequest->income,
            'scope_of_services' => $taskRequest->scope_of_services
        ]);

        foreach ($taskRequest->viewActivities as $activity) {
            $orderActivity = new TaskActivity([
                'view_activity_id' => $activity['viewActivityId'],
                'count' => $activity['count'],
                'date_start' => $activity['dateStart'],
                'date_end' => $activity['dateEnd'],
                'need_foto' => $activity['needFoto'],
                'date_activity' => $this->processDateActivity($activity['dateActivity']),
            ]);

            $task->taskActivities()->save($orderActivity);
        }

        return $task;
    }

    public function updateTask(CreateTaskRequest $taskRequest): Task
    {
        $task = Task::findOrFail($taskRequest->taskId);

        $task->update([
            'place_id' => $taskRequest->placeId,
            'self_employed' => $taskRequest->selfEmployed,
            'price' => $taskRequest->price,
            'income' => $taskRequest->income,
            'scope_of_services' => $taskRequest->scope_of_services
        ]);

        $task->taskActivities()->delete();

        foreach ($taskRequest->viewActivities as $activity) {
            $newActivity = new TaskActivity([
                'view_activity_id' => $activity['viewActivityId'],
                'count' => $activity['count'],
                'date_start' => $activity['dateStart'],
                'date_end' => $activity['dateEnd'],
                'need_foto' => $activity['needFoto'],
                'date_activity' => $this->processDateActivity($activity['dateActivity']),
            ]);
            $task->taskActivities()->save($newActivity);
        }

        return $task->fresh();
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

    public function getUserOrderByStatusPaginate(?OrderStatusEnum $status, int $userId, int $page = 1,int $perPage = 10): Paginator
    {
        return Order::query()->where('user_id',$userId)
            ->when($status, function ($q) use ($status) {
                return $q->where('status', $status->value);
            })->simplePaginate($perPage);
    }

    public function getUserOrderByStatus(int $userId, int|null $orderId): Order|null
    {
        if($orderId) {
            return Order::where('user_id', $userId)->where('id', $orderId)->first();
        }
        return null;
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

    public function getOrderByUserSyncDataPaginate(User $user,?OrderStatusEnum $status, int $page = 1, int $perPage = 10): Paginator
    {
        $place = $user->place?->pluck('id')->toArray();

        return Order::query()
            ->where(function ($query) use ($status, $place) {
                $query->when($status, fn($q) => $q->where('status', $status->value))
                    ->when($place, fn($q) => $q->whereIn('place_id', $place))
                ->where('status','!=',OrderStatusEnum::accepted->value);
            })
            ->orWhere(function ($query) use ($user,$status) {
                $userIdsSupervisor = $user->supervisors->pluck('id')->toArray();
                $userIdsSupervisor[] = $user->id;
                $query = $query->whereIn('accept_user_id',$userIdsSupervisor);
                if($status == OrderStatusEnum::accepted) {
                    $query->where('status', OrderStatusEnum::accepted->value);
                }
            })
            ->simplePaginate($perPage);
    }

    public function getOrderByUserSyncData(User $user, int|null $orderId): Order|null
    {

        $place = $user->place?->pluck('id')->toArray();

        if($orderId) {
            return Order
                ::where(function ($query) use ($place, $orderId) {
                    $query->when($place, fn($q) => $q->whereIn('place_id', $place))
                        ->where('status', '!=', OrderStatusEnum::accepted->value)
                        ->where('id', $orderId);
                })
                ->orWhere(function ($query) use ($user, $orderId) {
                    $userIdsSupervisor = $user->supervisors->pluck('id')->toArray();
                    $userIdsSupervisor[] = $user->id;
                    $query->whereIn('accept_user_id',$userIdsSupervisor)
                        ->where('id', $orderId);
                })
                ->first();
        }
        return null;
    }

    public function acceptedOrder(User $user, int $orderId): bool
    {
        return (bool)Order::query()
            ->where('id',$orderId)
            ->update(
                [
                    'status'=>OrderStatusEnum::accepted,
                    'accept_user_id' => $user->id
                ]
            );
    }

    public function convertTask(User $user, ConvertTaskRequest $request): Task
    {
        $order = Order::where('id',$request->orderId)->first();
        $countTaskForUser = $order->tasks?->where('user_id',$user->id)->count();
        if($countTaskForUser == 0) {
            $task = new Task([
                'place_id' => $order->place_id,
                'user_id' => $user->id,
                'self_employed' => $order->self_employed,
                'status' => OrderStatusEnum::accepted,
                'order_id' => $order->id,
                'price' => 0,
                'income' => 0,
                'scope_of_services' => 0,
                'accept_user_id' => $request->supervisorId ?? $user->id,
                'specialist_user_id' => null
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

    public function getTaskByUserSyncDataPaginate(User $user, ?OrderStatusEnum $status, int $page = 1, int $perPage = 10): Paginator
    {
        $userIdsSupervisor = $user->supervisors->pluck('id')->toArray();
        $userIdsSupervisor[] = $user->id;
        return Task::query()
            ->orWhere(function ($query) use ($user,$status,$userIdsSupervisor) {
                $query = $query->whereIn('user_id', $userIdsSupervisor);
                $query->where('status', $status->value);
            })
            ->orWhere(function ($query) use ($user,$status,$userIdsSupervisor) {
                $query = $query->whereIn('accept_user_id', $userIdsSupervisor);
                $query->where('status', $status->value);
            })
            ->orWhere(function ($query) use ($user,$status) {
                $userIdsSupervisor = $user->acceptedTasks?->pluck('id')->toArray();
                $query = $query->whereIn('id', $userIdsSupervisor);
                $query->where('status', $status->value);
            })
            ->simplePaginate($perPage);
    }

    public function getTaskByUserSyncData(User $user, ?int $taskId): Task|null
    {
        $userIdsSupervisor = $user->supervisors->pluck('id')->toArray();
        $userIdsSupervisor[] = $user->id;
        if($taskId){
            return Task::where(function ($query) use ($user,$taskId,$userIdsSupervisor) {
                    $query->whereIn('user_id', $userIdsSupervisor)->where('id', $taskId);
                })
                ->orWhere(function ($query) use ($user,$taskId,$userIdsSupervisor) {
                    $query->whereIn('accept_user_id', $userIdsSupervisor)->where('id', $taskId);
                })
                ->orWhere(function ($query) use ($user,$taskId) {
                    $userIdsSupervisor = $user->acceptedTasks?->pluck('id')->toArray();
                    $query->whereIn('id', $userIdsSupervisor)->where('id', $taskId);
                })->first();
        }
        return null;
    }

    public function instructTask(int $taskId,?array $supervisorIds): bool
    {
        if($supervisorIds) {
            $task = Task::query()->where('id', $taskId)->first();
            $task->status = OrderStatusEnum::notAccepted;
            $task->acceptingUsers()->syncWithoutDetaching($supervisorIds);
            $task->save();
        }
        return true;
    }

    public function invoiceTask(int $taskId,?array $supervisorIds): bool
    {
        $task = Task::query()->where('id',$taskId)->first();
        $task->status = OrderStatusEnum::accepted;
        if(count($supervisorIds)>0) {
            $task->accept_user_id = current($supervisorIds);
        }else{
            $task->accept_user_id = Auth::user()->id;
        }
        $task->save();
        return true;
    }

    public function cancelTask(int $taskId): bool
    {
        $task = Task::query()->where('id',$taskId)->first();
        $task->status = OrderStatusEnum::canceled;
        $task->save();
        return true;
    }

    public function acceptTask(User $user, int $taskId): bool
    {
         (bool)Task::query()
            ->where('id',$taskId)
            ->update(
                [
                    'status'=>OrderStatusEnum::accepted,
                    'accept_user_id' => $user->id
                ]
            );

        return true;
    }

    public function createBidFromOrder(User $user, int $orderId, int $orderActivityId): Bid
    {
        $order = Order::where('id', $orderId)->first();
        $bid = $order->bids?->where('activity_id', $orderActivityId)->first();
        if(!$bid) {
            $orderActivities = OrderActivities::where('id',$orderActivityId)->first();
            $bid = new Bid([
                'place_id' => $order->place_id,
                'user_id' => $user->id,
                'accept_user_id' => null,
                'order_id' => $order->id,
                'task_id' => null,
                'status' => OrderStatusEnum::notAccepted,
                'self_employed' => $order->self_employed,
                'radius' => null,
                'price' => null,
                'view_activity_id' => $orderActivities->view_activity_id,
                'count' => $orderActivities->count,
                'date_start' => $orderActivities->date_start,
                'date_end' => $orderActivities->date_end,
                'need_foto' => $orderActivities->need_foto,
                'date_activity' => $orderActivities->date_activity,
                'activity_id' => $orderActivityId
            ]);

            $bid->save();
        }

        return $bid;
    }

    public function createBidFromTask(User $user, int $taskId, int $taskActivityId): Bid
    {
        $task = Task::where('id', $taskId)->first();
        $bid = $task->bid?->where('activity_id', $taskActivityId)->first();
        if(!$bid) {
            $taskActivities = OrderActivities::where('id',$taskActivityId)->first();
            $bid = new Bid([
                'place_id' => $task->place_id,
                'user_id' => $user->id,
                'accept_user_id' => null,
                'order_id' => null,
                'task_id' => $task->id,
                'status' => OrderStatusEnum::notAccepted,
                'self_employed' => $task->self_employed,
                'radius' => null,
                'price' => null,
                'view_activity_id' => $taskActivities->view_activity_id,
                'count' => $taskActivities->count,
                'date_start' => $taskActivities->date_start,
                'date_end' => $taskActivities->date_end,
                'need_foto' => $taskActivities->need_foto,
                'date_activity' => $taskActivities->date_activity,
                'activity_id' => $taskActivityId
            ]);

            $bid->save();
        }

        return $bid;

    }

    public function getBidsByUserSyncDataPaginate(User $user, ?OrderStatusEnum $status, int $page = 1, int $perPage = 10): Paginator
    {
        $userIdsSupervisor = $user->supervisors->pluck('id')->toArray();
        $userIdsSupervisor[] = $user->id;

        return Bid::query()
            ->orWhere(function ($query) use ($user,$status,$userIdsSupervisor) {
                $query = $query->whereIn('user_id', $userIdsSupervisor);
                $query->where('status', $status->value);
            })
            ->orWhere(function ($query) use ($user,$status,$userIdsSupervisor) {
                $query = $query->whereIn('accept_user_id', $userIdsSupervisor);
                $query->where('status', $status->value);
            })
            ->orWhere(function ($query) use ($user,$status) {
                $userIdsSupervisor = $user->acceptedTasks?->pluck('id')->toArray();
                $query = $query->whereIn('id', $userIdsSupervisor);
                $query->where('status', $status->value);
            })
            ->simplePaginate($perPage);
    }

    public function getBidByUserSyncData(User $user, ?int $bidId): Bid|null
    {
        $userIdsSupervisor = $user->supervisors->pluck('id')->toArray();
        $userIdsSupervisor[] = $user->id;
        if($bidId){
            return Bid::where(function ($query) use ($user,$bidId,$userIdsSupervisor) {
                $query->whereIn('user_id', $userIdsSupervisor)->where('id', $bidId);
            })
                ->orWhere(function ($query) use ($user,$bidId,$userIdsSupervisor) {
                    $query->whereIn('accept_user_id', $userIdsSupervisor)->where('id', $bidId);
                })
                ->orWhere(function ($query) use ($user,$bidId) {
                    $userIdsSupervisor = $user->acceptedTasks?->pluck('id')->toArray();
                    $query->whereIn('id', $userIdsSupervisor)->where('id', $bidId);
                })->first();
        }
        return null;
    }

    public function invoiceBid(int $bidId, ?array $specialistIds): bool
    {
        $bid = Bid::query()->where('id',$bidId)->first();
        $bid->status = OrderStatusEnum::accepted;
        if(count($specialistIds)>0) {
            $bid->accept_user_id = current($specialistIds);
        }else{
            $bid->accept_user_id = Auth::user()->id;
        }
        $bid->save();
        return true;
    }

    public function acceptBid(User $user, int $bidId): bool
    {
        (bool)Bid::query()
            ->where('id',$bidId)
            ->update(
                [
                    'status'=>OrderStatusEnum::accepted,
                    'accept_user_id' => $user->id
                ]
            );

        return true;
    }

    public function instructBid(int $bidId, ?array $specialistIds): bool
    {
        if($specialistIds) {
            $bid = Bid::query()->where('id', $bidId)->first();
            $bid->status = OrderStatusEnum::notAccepted;
            $bid->acceptingUsers()->syncWithoutDetaching($specialistIds);
            $bid->save();
        }
        return true;
    }

    public function cancelBid(int $bidId): bool
    {
        $bid = Bid::query()->where('id',$bidId)->first();
        $bid->status = OrderStatusEnum::canceled;
        $bid->save();
        return true;
    }

    public function getSpecialistForBid(int $bidId): Collection
    {
        $bid = Bid::query()->where('id',$bidId)->first();
        $place = $bid->place;
        if($bid->self_employed){
            $status = TaxStatus::query()->where('id',2)->first()?->uuid;
        }else{
            $status = TaxStatus::query()->where('id',1)->first()?->uuid;
        }
        $fieldView = Fields::where('directory',ViewActivities::class)->first();
        $fieldStat = Fields::where('directory',TaxStatus::class)->first();
        if($fieldView && $bid->viewActivity && $place) {
            $users = User::whereJsonContains('data->'.$fieldView->uuid, $bid->viewActivity->uuid)
                ->where('data->'.$fieldStat->uuid, $status)
                ->get();
            $userInRadius = collect();
            foreach ($users as $user) {
                if (CoordinatesService::isPointInRadius(
                    $user->latitude,
                    $user->longitude,
                    $place->latitude,
                    $place->longitude,
                    $user->mapRadius
                )) {
                    $userInRadius->push($user);
                }
            }
        }
        return $userInRadius;
    }

    public function updateBid(BidDataRequest $bidRequest): Bid
    {
        $bid = Bid::where('id',$bidRequest->bidId)->first();
        $bid->radius = $bidRequest->radius;
        $bid->price = $bidRequest->price;
        $bid->save();
        return $bid;
    }
}
