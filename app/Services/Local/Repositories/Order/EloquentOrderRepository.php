<?php

namespace App\Services\Local\Repositories\Order;

use App\Enum\Order\BidAcceptingStatusEnum;
use App\Enum\Order\OrderStatusEnum;
use App\Enum\Role\RoleEnum;
use App\Http\Requests\Order\BidDataRequest;
use App\Http\Requests\Order\CancelRequestRequest;
use App\Http\Requests\Order\ConvertTaskRequest;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\CreateRequestFromBidRequest;
use App\Http\Requests\Order\CreateRequestFromTaskRequest;
use App\Http\Requests\Order\CreateTaskActivityRequest;
use App\Http\Requests\Order\DeleteOrderActivityRequest;
use App\Http\Requests\Order\DeleteTaskActivityRequest;
use App\Http\Requests\Order\RepeatTaskRequest;
use App\Http\Requests\Order\UpdateOrderActivityRequest;
use App\Http\Requests\Order\UpdateTaskActivityRequest;
use App\Http\Requests\Order\UpdateTaskRequest;
use App\Models\Fields\Directory\Radius;
use App\Models\Order\Bid;
use App\Models\Order\Order;
use App\Models\Order\OrderActivities;
use App\Models\Order\Request;
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
use App\Http\Requests\Order\RepeatOrderRequest;

class EloquentOrderRepository implements OrderRepository
{
    public function createOrder(CreateOrderRequest $orderRequest, int $userId): Order
    {
        $order = Order::create([
            'place_id' => $orderRequest->placeId,
            'user_id' => $userId,
            'self_employed' => $orderRequest->selfEmployed ?? false,
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
            'date_activity' => $this->processDateActivity($request->dateActivity ?? []),
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

        $viewActivities = $order->place->project
            ->flatMap(fn($project) => $project->viewActivities)
            ->unique('id');
        $viewActivities = $viewActivities->where('self_employed', $order->self_employed);

        DB::transaction(function () use ($order, $orderRequest) {
            $order->update([
                'place_id' => $orderRequest->placeId??$order->placeId,
                'self_employed' => $orderRequest->selfEmployed??$order->selfEmployed,
            ]);
        });

        if($orderRequest->placeId){
            $order = Order::where('id', $orderRequest->orderId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $viewActivitiesNew = $order->place->project
                ->flatMap(fn($project) => $project->viewActivities)
                ->unique('id');
            $viewActivitiesNew = $viewActivitiesNew->where('self_employed', $order->self_employed);
            $result = $viewActivities->diff($viewActivitiesNew->keyBy('id'));
            if($result->isNotEmpty()){
                OrderActivities::whereIn('id',$result->pluck('id')->toArray())->delete();
            }

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
            'project_id' => $taskRequest->projectId,
            'user_id' => $userId,
            'self_employed' => $taskRequest->selfEmployed ?? false,
            'status' => OrderStatusEnum::new->value,
            'specialist_user_id' => null,
            'accept_user_id' => null,
            'order_id' => null,
            'price' => 0,
            'income' => 0,
            'scope_of_services' => 0,
//            'price' => $taskRequest->price??0,
//            'income' => $taskRequest->income??0,
//            'scope_of_services' => $taskRequest->scope_of_services??0
        ]);

        return $task;
    }

    public function createTaskActivity(CreateTaskActivityRequest $taskRequest): Task
    {
        $taskActivity = new TaskActivity([
            'view_activity_id' => $taskRequest->viewActivityId,
            'count' => $taskRequest->count,
            'date_start' => $taskRequest->dateStart,
            'date_end' => $taskRequest->dateEnd,
            'need_foto' => $taskRequest->needFoto,
            'date_activity' => $this->processDateActivity($taskRequest->dateActivity ?? []),
            'task_id' => $taskRequest->taskId
        ]);

        $taskActivity->save();

        return Task::where('id',$taskRequest->taskId)->first();
    }

    public function updateTask(UpdateTaskRequest $taskRequest): Task
    {
        $task = Task::findOrFail($taskRequest->taskId);

        if($task->project){
            $viewActivities = $task->project->viewActivities
                ->unique('id');
        }

        $task->update([
            'place_id' => $taskRequest->placeId??$task->place_id,
            'project_id' => $taskRequest->projectId??$task->project_id,
            'self_employed' => $taskRequest->selfEmployed??$task->self_employed,
//        'price' => $taskRequest->price??$task->price,
//            'income' => $taskRequest->income??$task->income,
//            'scope_of_services' => $taskRequest->scope_of_services??$task->scope_of_services
        ]);

        if($taskRequest->projectId){
            $task = Task::findOrFail($taskRequest->taskId);

            $viewActivitiesNew = $task->project->viewActivities
                ->unique('id');
            $viewActivitiesNew = $viewActivitiesNew->where('self_employed', $task->self_employed);
            $result = $viewActivities->diff($viewActivitiesNew->keyBy('id'));
            if($result->isNotEmpty()){
                TaskActivity::whereIn('id',$result->pluck('id')->toArray())->delete();
            }
        }

        return $task->fresh();
    }

    public function deleteTaskActivity(DeleteTaskActivityRequest $taskRequest): Task
    {
        TaskActivity::where('id',$taskRequest->taskActivityId)->where('task_id',$taskRequest->taskId)->delete();
        return Task::where('id',$taskRequest->taskId)->first();
    }

    private function processDateActivity(array $dateActivities = []): array
    {

        return array_map(function ($item) {
            return [
                'timeStart' => $item['timeStart']??'',
                'timeEnd' => $item['timeEnd']??'',
                'placeIds' => $item['placeIds']??[],
            ];
        }, $dateActivities);
    }

    public function getUserOrderByStatusPaginate(?OrderStatusEnum $status, int $userId): Collection
    {
        return Order::query()->where('user_id',$userId)
            ->when($status, function ($q) use ($status) {
                return $q->where('status', $status->value);
            })->get();
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

    public function getOrderByUserSyncDataPaginate(User $user,?OrderStatusEnum $status): Collection
    {
        $place = $user->place?->pluck('id')->toArray();

        return Order::query()
            ->where(function ($query) use ($status, $place) {
                $query->when($status, fn($q) => $q->where('status', $status->value))
                    ->when($place, fn($q) => $q->whereIn('place_id', $place))
                    ->where('status','!=',OrderStatusEnum::accepted->value)
                    ->has('orderActivities');
            })
            ->orWhere(function ($query) use ($user,$status) {
                $userIdsSupervisor = $user->supervisors->pluck('id')->toArray();
                $userIdsSupervisor[] = $user->id;
                $query = $query->whereIn('accept_user_id',$userIdsSupervisor);
                if($status == OrderStatusEnum::accepted) {
                    $query = $query->where('status', OrderStatusEnum::accepted->value);
                }
                $query->has('orderActivities');
            })->get();
    }

    public function getOrderByUserSyncData(User $user, int|null $orderId): Order|null
    {

        $place = $user->place?->pluck('id')->toArray();

        if($orderId) {
            return Order
                ::where(function ($query) use ($place, $orderId) {
                    $query->when($place, fn($q) => $q->whereIn('place_id', $place))
                        ->where('status', '!=', OrderStatusEnum::accepted->value)
                        ->where('id', $orderId)
                        ->has('orderActivities');
                })
                ->orWhere(function ($query) use ($user, $orderId) {
                    $userIdsSupervisor = $user->supervisors->pluck('id')->toArray();
                    $userIdsSupervisor[] = $user->id;
                    $query->whereIn('accept_user_id',$userIdsSupervisor)
                        ->where('id', $orderId)
                        ->has('orderActivities');
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
                    'status'=>OrderStatusEnum::accepted->value,
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
                'status' => OrderStatusEnum::accepted->value,
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

    public function getTaskByUserSyncDataPaginate(User $user, ?OrderStatusEnum $status): Collection
    {
        $userIdsSupervisor = $user->supervisors->pluck('id')->toArray();
        $userIdsSupervisor[] = $user->id;
        return Task::query()
            ->orWhere(function ($query) use ($user,$status,$userIdsSupervisor) {
                $query = $query->whereIn('user_id', $userIdsSupervisor);
                if ($status) {
                    $query->where('status', $status->value);
                }
            })
            ->orWhere(function ($query) use ($user,$status,$userIdsSupervisor) {
                $query = $query->whereIn('accept_user_id', $userIdsSupervisor);
                if ($status) {
                    $query->where('status', $status->value);
                }
            })
            ->orWhere(function ($query) use ($user,$status) {
                $userIdsSupervisor = $user->acceptedTasks?->pluck('id')->toArray();
                $query = $query->whereIn('id', $userIdsSupervisor);
                if ($status) {
                    $query->where('status', $status->value);
                }
            })->get();
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
        $task = Task::query()->where('id',$taskId)->first();
        $task->status = OrderStatusEnum::accepted->value;
        if(count($supervisorIds)>0) {
            $task->accept_user_id = current($supervisorIds);
        }else{
            $task->accept_user_id = Auth::user()->id;
        }
        $task->save();
        return true;
    }

    public function invoiceTask(int $taskId,?array $supervisorIds): bool
    {
        if($supervisorIds) {
            $task = Task::query()->where('id', $taskId)->first();
            $task->status = OrderStatusEnum::notAccepted->value;
            $task->acceptingUsers()->syncWithoutDetaching($supervisorIds);
            $task->save();
        }
        return true;
    }

    public function cancelTask(int $taskId): bool
    {
        $task = Task::query()->where('id',$taskId)->first();
        $task->status = OrderStatusEnum::canceled->value;
        $task->save();
        return true;
    }

    public function acceptTask(User $user, int $taskId): bool
    {
        return (bool)Task::query()
            ->where('id',$taskId)
            ->update(
                [
                    'status'=>OrderStatusEnum::accepted->value,
                    'accept_user_id' => $user->id
                ]
            );
    }

    public function createBidFromOrder(User $user, int $orderId, int $orderActivityId): Bid
    {
        $order = Order::where('id', $orderId)->first();
        $bid = $order->bids?->where('activity_id', $orderActivityId)->first();
        if (!$bid) {
            $orderActivities = OrderActivities::where('id', $orderActivityId)->first();
            $bid             = new Bid();
            $radius = Radius::where('default',true)->first();
            if(!$radius) {
                $bid->radius = 5;
            }else{
                $bid->radius = $radius->value;
            }
            $bid->place_id         = $order->place_id;
            $bid->user_id          = $user->id;
            $bid->accept_user_id   = null;
            $bid->order_id         = $order->id;
            $bid->task_id          = null;
            $bid->status           = OrderStatusEnum::new->value;
            $bid->self_employed    = $order->self_employed;
            $bid->price            = null;
            $bid->view_activity_id = $orderActivities->view_activity_id;
            $bid->count            = $orderActivities->count;
            $bid->date_start       = $orderActivities->date_start;
            $bid->date_end         = $orderActivities->date_end;
            $bid->need_foto        = $orderActivities->need_foto;
            $bid->date_activity    = $orderActivities->date_activity;
            $bid->activity_id      = $orderActivityId;


            $bid->save();
        }

        return $bid;
    }

    public function createBidFromTask(User $user, int $taskId, int $taskActivityId): Bid
    {
        $task = Task::where('id', $taskId)->first();
        $bid = $task->bid?->where('activity_id', $taskActivityId)->first();
        if (!$bid) {
            $taskActivities = TaskActivity::where('id', $taskActivityId)->first();
            $bid            = new Bid();

            $radius = Radius::where('default',true)->first();
            if(!$radius) {
                $bid->radius = 5;
            }else{
                $bid->radius = $radius->value;
            }

            $bid->place_id         = $task->place_id;
            $bid->user_id          = $user->id;
            $bid->accept_user_id   = null;
            $bid->order_id         = null;
            $bid->task_id          = $task->id;
            $bid->status           = OrderStatusEnum::new->value;
            $bid->self_employed    = $task->self_employed;
            $bid->price            = null;
            $bid->view_activity_id = $taskActivities->view_activity_id;
            $bid->count            = $taskActivities->count;
            $bid->date_start       = $taskActivities->date_start;
            $bid->date_end         = $taskActivities->date_end;
            $bid->need_foto        = $taskActivities->need_foto;
            $bid->date_activity    = $taskActivities->date_activity;
            $bid->activity_id      = $taskActivityId;


            $bid->save();
        }

        return $bid;

    }

    public function getBidsByUserSyncDataPaginate(User $user, ?OrderStatusEnum $status): Collection
    {
        $userIdsSupervisor = $user->supervisors->pluck('id')->toArray();
        $userIdsSupervisor[] = $user->id;

        return Bid::query()
            ->orWhere(function ($query) use ($user,$status,$userIdsSupervisor) {
                $query = $query->whereIn('user_id', $userIdsSupervisor);
                if($status) {
                    $query->where('status', $status->value);
                }
            })
            ->orWhere(function ($query) use ($user,$status) {
                $userIdsSupervisor = $user->acceptedBids?->pluck('id')->toArray();
                $query = $query->whereIn('id', $userIdsSupervisor);
                if($status) {
                    $query->where('status', $status->value);
                }
            })->get();
    }

    public function getBidByUserSyncData(User $user, ?int $bidId): Bid|null
    {
        $userIdsSupervisor = $user->supervisors->pluck('id')->toArray();
        $userIdsSupervisor[] = $user->id;
        if($bidId){
            return Bid::where(function ($query) use ($user,$bidId,$userIdsSupervisor) {
                $query->whereIn('user_id', $userIdsSupervisor)->where('id', $bidId);
            })
                ->orWhere(function ($query) use ($user,$bidId) {
                    $userIdsSupervisor = $user->acceptedBids?->pluck('id')->toArray();
                    $query->whereIn('id', $userIdsSupervisor)->where('id', $bidId);
                })->first();
        }
        return null;
    }

    public function invoiceBid(int $bidId, ?array $specialistIds): bool
    {
        if($specialistIds) {
            /** @var Bid $bid  */
            $bid = Bid::query()->where('id', $bidId)->first();
            $bid->status = OrderStatusEnum::notAccepted->value;
            /** @var User $user  */
            $user = User::where('id',Auth::id())->first();
            $userRoles = $user->roles?->pluck('id')->toArray();
            if(in_array(RoleEnum::manager->value,$userRoles)){
                $specialistIdsFromRelated = $user->managerSpecialist()->allRelatedIds()->toArray();
                if(!empty($user->supervisors)) {
                    $arrSpecialist = [];
                    foreach ($user->supervisors as $supervisor) {
                        $arrSpecialist[] = $supervisor->supervisorSpecialist()->allRelatedIds()->toArray();
                    }
                    $specialistIdsFromRelated = array_merge($specialistIdsFromRelated,...$arrSpecialist);
                }
            }
            if(in_array(RoleEnum::supervisor->value,$userRoles)){
                $specialistIdsFromRelated = $user->supervisorSpecialist()->allRelatedIds()->toArray();
            }
            $commonIds = array_intersect($specialistIdsFromRelated, $specialistIds);

            $uniqueToSpecialistIds = array_diff($specialistIds, $specialistIdsFromRelated);

            if($uniqueToSpecialistIds) {
                $bid->acceptingUsers()->syncWithoutDetaching(
                    collect($uniqueToSpecialistIds)->mapWithKeys(function ($id) use ($bid) {
                        return [
                            $id => [
                                'accepted' => BidAcceptingStatusEnum::notAccepted->value,
                                'task_id'  => $bid->task_id,
                                'order_id' => $bid->order_id,
                                'user_id_maintainer'  => Auth::id()
                            ]
                        ];
                    })->toArray()
                );
            }

            if($commonIds) {
                $bid->acceptingUsers()->syncWithoutDetaching(
                    collect($commonIds)->mapWithKeys(function ($id) use ($bid,$commonIds) {
                        if (count($commonIds) >= $bid->count) {
                            $bid->status = OrderStatusEnum::accepted->value;
                            $bid->save();
                        }
                        return [
                            $id => [
                                'accepted' => BidAcceptingStatusEnum::accepted->value,
                                'task_id'  => $bid->task_id,
                                'order_id' => $bid->order_id,
                                'user_id_maintainer'  => Auth::id()
                            ]
                        ];
                    })->toArray()
                );
            }
            $bid->save();
        }
        return true;
    }

    public function acceptBid(User $user, int $bidId): bool
    {
        $bid = Bid::query()->where('id', $bidId)->first();
        /** @var Bid $bid  */
        $count = $bid->acceptingUsers()->count();
        if ($bid->acceptingUsers()->where('user_id', $user->id)->exists()) {
            $updated = $bid->acceptingUsers()->updateExistingPivot(
                $user->id,
                [
                    'accepted' => BidAcceptingStatusEnum::consideration->value,
                    'task_id' => $bid->task_id,
                    'order_id' => $bid->order_id
                ]
            );
            if($updated) {
                if (($count + 1) >= $bid->count) {
                    $bid->status = OrderStatusEnum::accepted->value;
                    $bid->save();
                }
            }
            return (bool)$updated;
        }
        if($count >= $bid->count) {
            $bid->status = OrderStatusEnum::accepted->value;
            $bid->save();
        }
        return false;
    }

    public function instructBid(int $bidId, int $specialistId): bool
    {
        $bid = Bid::query()->where('id', $bidId)->first();
        /** @var Bid $bid  */
        $count = $bid->acceptingUsers()->count();
        if ($bid->acceptingUsers()->where('user_id', $specialistId)->exists()) {
            $updated = $bid->acceptingUsers()->updateExistingPivot(
                $specialistId,
                [
                    'accepted' => BidAcceptingStatusEnum::accepted->value,
                    'task_id' => $bid->task_id,
                    'order_id' => $bid->order_id
                ]
            );
            if($updated) {
                if (($count + 1) >= $bid->count) {
                    $bid->status = OrderStatusEnum::accepted->value;
                    $bid->save();
                }
            }
            return (bool)$updated;
        } else {
            $bid->acceptingUsers()->attach($specialistId, [
                'accepted' => BidAcceptingStatusEnum::accepted->value,
                'task_id' => $bid->task_id,
                'order_id' => $bid->order_id
            ]);
            if (($count + 1) >= $bid->count) {
                $bid->status = OrderStatusEnum::accepted->value;
                $bid->save();
            }
            return true;
        }
    }

    public function cancelBid(int $bidId): bool
    {
        $bid = Bid::query()->where('id',$bidId)->first();
        $bid->status = OrderStatusEnum::canceled->value;
        $bid->save();
        return true;
    }

    public function getSpecialistForBid(int $bidId): Collection
    {
        $bid = Bid::query()->where('id',$bidId)->first();
        $place = $bid->place;
        $status = [];
        $status[] = TaxStatus::query()->where('id',1)->first()?->uuid;
        if($bid->self_employed){
            $status[] = TaxStatus::query()->where('id',2)->first()?->uuid;
        }
        $fieldView = Fields::where('directory',ViewActivities::class)->first();
        $fieldStat = Fields::where('directory',TaxStatus::class)->first();

        if($fieldView && $bid->viewActivity && $place) {
            $users = User::whereJsonContains('data->'.$fieldView->uuid, $bid->viewActivity->uuid)
                ->whereIn('data->'.$fieldStat->uuid, $status)
                ->get();

            $userInRadius = collect();
            $radius = Radius::where('default',true)->first();
            if(!$radius) {
                $radius = 5;
            }else{
                $radius = $radius->value;
            }

            foreach ($users as $user) {
                if (
                    $user->latitude && $user->longitude && $place->latitude && $place->longitude &&
                    CoordinatesService::isPointInRadius(
                        $user->latitude,
                        $user->longitude,
                        $place->latitude,
                        $place->longitude,
                        $user->mapRadius ?: $radius,
                        $bid->radius
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
        $radius = Radius::where('default',true)->first();
        if(!$radius) {
            if(!$bid->radius) {
                $bid->radius = 5;
            }
        }else{
            $bid->radius = $radius->value;
        }
        $bid->radius = $bidRequest->radius??$bid->radius;
        $bid->price = $bidRequest->price??$bid->price;
        $bid->view_activity_id = $bidRequest->viewActivityId ?? $bid->view_activity_id;
        $bid->count = $bidRequest->count ?? $bid->count;
        $bid->date_start = $bidRequest->dateStart ?? $bid->date_start;
        $bid->date_end = $bidRequest->dateEnd ?? $bid->date_end;
        $bid->need_foto = $bidRequest->needFoto ?? $bid->need_foto;
        $bid->date_activity = $bidRequest->dateActivity ? $this->processDateActivity($bidRequest->dateActivity) : $bid->date_activity;
        $bid->save();
        return $bid;
    }

    public function rejectBid(User $user, int $bidId): bool
    {
        $bid = Bid::where('id',$bidId)->first();
        $bid->acceptingUsers()->updateExistingPivot(
            $user->id,
            [
                'accepted' => BidAcceptingStatusEnum::declined->value,
                'task_id' => $bid->task_id,
                'order_id' => $bid->order_id
            ]
        );
        return true;
    }

    public function createRequestFromTask(CreateRequestFromTaskRequest $request, User $user): Request
    {
        $task = Task::where('id',$request->taskId)->first();
        $requestModel = new Request();
        $requestModel->place_id = $task->place_id;
        $requestModel->user_id = $user->id;
        $requestModel->accept_user_id = null;
        $requestModel->order_id = $task->order_id;
        $requestModel->task_id = $task->id;
        $requestModel->status = OrderStatusEnum::notAccepted->value;
        $requestModel->self_employed = $task->self_employed;
        //$requestModel->radius = $task->radius;??
        $requestModel->price = $task->price;

        $activity = TaskActivity::where('id',$request->taskActivityId)->first();
        $requestModel->view_activity_id = $activity->view_activity_id;
        $requestModel->count = $activity->count;
        $requestModel->date_start = $activity->date_start;
        $requestModel->date_end = $activity->date_end;
        $requestModel->need_foto = $activity->need_foto;
        $requestModel->date_activity = $activity->date_activity;
        $requestModel->activity_id = $request->taskActivityId;

        $requestModel->save();

        return $requestModel;
    }

    public function createRequestFromBid(CreateRequestFromBidRequest $request, User $user): Request
    {
        $bid = Bid::where('id', $request->bidId)->first();
        $requestModel = new Request();
        $requestModel->place_id = $bid->place_id;
        $requestModel->user_id = $user->id;
        $requestModel->accept_user_id = null;
        $requestModel->order_id = $bid->order_id;
        $requestModel->task_id = $bid->id;
        $requestModel->status = OrderStatusEnum::notAccepted->value;
        $requestModel->self_employed = $bid->self_employed;
        $requestModel->radius = $bid->radius;
        $requestModel->price = $bid->price;
        $requestModel->view_activity_id = $bid->view_activity_id;
        $requestModel->count = $bid->count;
        $requestModel->date_start = $bid->date_start;
        $requestModel->date_end = $bid->date_end;
        $requestModel->need_foto = $bid->need_foto;
        $requestModel->date_activity = $bid->date_activity;
        $requestModel->activity_id = $bid->taskActivityId;
        $requestModel->save();

        return $requestModel;
    }

    public function cancelRequest(CancelRequestRequest $request): bool
    {
        return (bool)Request::query()
            ->where('id',$request->requestId)
            ->update(
                [
                    'status'=>OrderStatusEnum::canceled->value,
                ]
            );
    }

    public function repeatTask(RepeatTaskRequest $request): Task
    {
        $task = Task::where('id',$request->taskId)->first();
        $newTask = new Task();
        $newTask->place_id = $task->place_id;
        $newTask->user_id = $task->user_id;
        $newTask->order_id = $task->order_id;
        $newTask->status = OrderStatusEnum::new->value;
        $newTask->self_employed = $task->self_employed;
        $newTask->price = $task->price;
        $newTask->income = $task->income;
        $newTask->scope_of_services = $task->scope_of_services;
        $newTask->project_id = $task->project_id;
        $newTask->save();
        $taskActivitiesOld = $task->taskActivities??[];
        foreach ($taskActivitiesOld as $taskActivityOld) {
            $taskActivity = new TaskActivity([
                'view_activity_id' => $taskActivityOld->view_activity_id,
                'count'            => $taskActivityOld->count,
                'date_start'       => $taskActivityOld->date_start,
                'date_end'         => $taskActivityOld->date_end,
                'need_foto'        => $taskActivityOld->need_foto,
                'date_activity'    => $taskActivityOld->date_activity,
                'task_id'          => $newTask->id
            ]);
            $taskActivity->save();
        }
        return $newTask->fresh();
    }

    public function repeatOrder(RepeatOrderRequest $request): Order
    {
        $order = Order::where('id',$request->orderId)->first();
        $newOrder = new Order();
        $newOrder->place_id = $order->place_id;
        $newOrder->user_id = $order->user_id;
        $newOrder->self_employed = $order->self_employed;
        $newOrder->status = OrderStatusEnum::new->value;
        $newOrder->save();

        $orderActivitiesOld = $order->orderActivities??[];
        foreach ($orderActivitiesOld as $orderActivityOld) {
            $orderActivity = new OrderActivities([
                'view_activity_id' => $orderActivityOld->view_activity_id,
                'count' => $orderActivityOld->count,
                'date_start' => $orderActivityOld->date_start,
                'date_end' => $orderActivityOld->date_end,
                'need_foto' => $orderActivityOld->need_foto,
                'date_activity' => $orderActivityOld->date_activity,
                'order_id' => $newOrder->id
            ]);
            $orderActivity->save();
        }
        return $newOrder->fresh();
    }

    public function updateTaskActivity(UpdateTaskActivityRequest $request): Task
    {
        $taskActivity = TaskActivity::where('id', $request->taskActivity)->first();

        $taskActivity->view_activity_id = $request->viewActivityId ?? $taskActivity->view_activity_id;
        $taskActivity->count            = $request->count ?? $taskActivity->count;
        $taskActivity->date_start       = $request->dateStart ?? $taskActivity->date_start;
        $taskActivity->date_end         = $request->dateEnd ?? $taskActivity->date_end;
        $taskActivity->need_foto        = $request->needFoto ?? $taskActivity->need_foto;
        $taskActivity->date_activity    = $request->dateActivity ? $this->processDateActivity($request->dateActivity) : $taskActivity->date_activity;
            $taskActivity->task_id = $request->taskId ?? $taskActivity->task_id;
        $taskActivity->save();

        return Task::where('id',$request->taskId)->first();
    }

    public function updateOrderActivity(UpdateOrderActivityRequest $request): Order
    {
        $orderActivity = OrderActivities::where('id', $request->orderActivity)->first();

        $orderActivity->view_activity_id = $request->viewActivityId ?? $orderActivity->view_activity_id;
        $orderActivity->count            = $request->count ?? $orderActivity->count;
        $orderActivity->date_start       = $request->dateStart ?? $orderActivity->date_start;
        $orderActivity->date_end         = $request->dateEnd ?? $orderActivity->date_end;
        $orderActivity->need_foto        = $request->needFoto ?? $orderActivity->need_foto;
        $orderActivity->date_activity    = $request->dateActivity ? $this->processDateActivity($request->dateActivity) : $orderActivity->date_activity;
        $orderActivity->order_id         = $request->orderI ?? $orderActivity->order_id;

        $orderActivity->save();

        return Order::where('id', $request->orderId)->first();
    }
}
