<?php

namespace App\Services\Local\Repositories\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\Order\BidDataRequest;
use App\Http\Requests\Order\CancelRequestRequest;
use App\Http\Requests\Order\ConvertTaskRequest;
use App\Http\Requests\Order\CreateOrderActivityRequest;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\CreateRequestFromBidRequest;
use App\Http\Requests\Order\CreateRequestFromTaskRequest;
use App\Http\Requests\Order\CreateTaskActivityRequest;
use App\Http\Requests\Order\CreateTaskRequest;
use App\Http\Requests\Order\DeleteOrderActivityRequest;
use App\Http\Requests\Order\DeleteTaskActivityRequest;
use App\Http\Requests\Order\RepeatOrderRequest;
use App\Http\Requests\Order\RepeatTaskRequest;
use App\Http\Requests\Order\UpdateOrderActivityRequest;
use App\Http\Requests\Order\UpdateTaskActivityRequest;
use App\Http\Requests\Order\UpdateTaskRequest;
use App\Models\Order\Bid;
use App\Models\Order\Order;
use App\Models\Order\Request;
use App\Models\Order\Task;
use App\Models\Order\TaskActivity;
use App\Models\User;
use App\Services\Local\Repositories\Contracts\OrderRepository;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use App\Http\Requests\Order\UpdateOrderRequest;

class CachingOrderRepository implements OrderRepository
{
    public function __construct(
        protected OrderRepository $orders,
        protected CacheManager $cache,
    ) {
    }


    public function createOrder(CreateOrderRequest $orderRequest, int $userId): Order
    {
        return $this->orders->createOrder($orderRequest,$userId);
    }

    public function getUserOrderByStatusPaginate(?OrderStatusEnum $status, int $userId): Collection
    {
        return $this->orders->getUserOrderByStatusPaginate($status,$userId);
    }

    public function cancelOrder(int $orderId): bool
    {
        return $this->orders->cancelOrder($orderId);
    }

    public function sendOrder(int $orderId): bool
    {
        return $this->orders->sendOrder($orderId);
    }

    public function updateOrder(UpdateOrderRequest $orderRequest): Order
    {
        return $this->orders->updateOrder($orderRequest);
    }

    public function getOrderByUserSyncDataPaginate(User $user,?OrderStatusEnum $status): Collection
    {
        return $this->orders->getOrderByUserSyncDataPaginate($user,$status);
    }

    public function getOrderByUserSyncData(User $user,int|null $orderId): Order|null
    {
        return $this->orders->getOrderByUserSyncData($user,$orderId);
    }

    public function acceptedOrder(User $user, int $orderId): bool
    {
        return $this->orders->acceptedOrder($user,$orderId);
    }

    public function convertTask(User $user, ConvertTaskRequest $request): Task
    {
        return $this->orders->convertTask($user,$request);
    }

    public function getTaskByUserSyncDataPaginate(User $user, ?OrderStatusEnum $status): Collection
    {
        return $this->orders->getTaskByUserSyncDataPaginate($user,$status);
    }

    public function getUserOrderByStatus(int $userId, ?int $orderId): Order|null
    {
        return $this->orders->getUserOrderByStatus($userId,$orderId);
    }

    public function getTaskByUserSyncData(User $user, ?int $taskId): Task|null
    {
        return $this->orders->getTaskByUserSyncData($user,$taskId);
    }

    public function createTask(CreateTaskRequest $taskRequest, int $userId): Task
    {
        return $this->orders->createTask($taskRequest,$userId);
    }

    public function updateTask(UpdateTaskRequest $taskRequest): Task
    {
        return $this->orders->updateTask($taskRequest);
    }

    public function instructTask(int $taskId, ?array $supervisorIds): bool
    {
        return $this->orders->instructTask($taskId,$supervisorIds);
    }

    public function invoiceTask(int $taskId, ?array $supervisorIds): bool
    {
        return $this->orders->invoiceTask($taskId,$supervisorIds);
    }

    public function cancelTask(int $taskId): bool
    {
        return $this->orders->cancelTask($taskId);
    }

    public function acceptTask(User $user, int $taskId): bool
    {
        return $this->orders->acceptTask($user,$taskId);
    }

    public function createBidFromOrder(User $user, int $orderId, int $orderActivityId): Bid
    {
        return $this->orders->createBidFromOrder($user,$orderId,$orderActivityId);
    }

    public function createBidFromTask(User $user, int $taskId, int $taskActivityId): Bid
    {
        return $this->orders->createBidFromTask($user,$taskId,$taskActivityId);
    }

    public function getBidsByUserSyncDataPaginate(User $user, ?OrderStatusEnum $status): Collection
    {
        return $this->orders->getBidsByUserSyncDataPaginate($user,$status);
    }

    public function getBidByUserSyncData(User $user, ?int $bidId): Bid|null
    {
        return $this->orders->getBidByUserSyncData($user,$bidId);
    }

    public function invoiceBid(int $bidId, ?array $specialistIds): bool
    {
        return $this->orders->invoiceBid($bidId,$specialistIds);
    }

    public function acceptBid(User $user, int $bidId): bool
    {
        return $this->orders->acceptBid($user,$bidId);
    }

    public function instructBid(int $bidId, ?int $specialistId): bool
    {
        return $this->orders->instructBid($bidId,$specialistId);
    }

    public function cancelBid(int $bidId): bool
    {
        return $this->orders->cancelBid($bidId);
    }

    public function getSpecialistForBid(int $bidId): Collection
    {
        return $this->orders->getSpecialistForBid($bidId);
    }

    public function updateBid(BidDataRequest $bidRequest): Bid
    {
        return $this->orders->updateBid($bidRequest);
    }

    public function createOrderActivity(CreateOrderActivityRequest $request): Order
    {
        return $this->orders->createOrderActivity($request);
    }

    public function deleteOrderActivity(DeleteOrderActivityRequest $orderRequest): Order
    {
        return $this->orders->deleteOrderActivity($orderRequest);
    }

    public function createTaskActivity(CreateTaskActivityRequest $taskRequest): Task
    {
        return $this->orders->createTaskActivity($taskRequest);
    }

    public function deleteTaskActivity(DeleteTaskActivityRequest $taskRequest): Task
    {
        return $this->orders->deleteTaskActivity($taskRequest);
    }

    public function rejectBid(User $user, int $bidId): bool
    {
        return $this->orders->rejectBid($user,$bidId);
    }

    public function createRequestFromTask(CreateRequestFromTaskRequest $request, User $user): Request
    {
        return $this->orders->createRequestFromTask($request,$user);
    }

    public function createRequestFromBid(CreateRequestFromBidRequest $request, User $user): Request
    {
        return $this->orders->createRequestFromBid($request,$user);
    }

    public function cancelRequest(CancelRequestRequest $request): bool
    {
        return $this->orders->cancelRequest($request);
    }

    public function repeatTask(RepeatTaskRequest $request): Task
    {
        return $this->orders->repeatTask($request);
    }

    public function repeatOrder(RepeatOrderRequest $request): Order
    {
        return $this->orders->repeatOrder($request);
    }

    public function updateTaskActivity(UpdateTaskActivityRequest $request): Task
    {
        return $this->orders->updateTaskActivity($request);
    }

    public function updateOrderActivity(UpdateOrderActivityRequest $request): Order
    {
        return $this->orders->updateOrderActivity($request);
    }
}
