<?php

namespace App\Services\Local\Repositories\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\Order\ConvertTaskRequest;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\CreateTaskRequest;
use App\Models\Order\Order;
use App\Models\Order\Task;
use App\Models\User;
use App\Services\Local\Repositories\Contracts\OrderRepository;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Pagination\Paginator;

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

    public function getUserOrderByStatusPaginate(?OrderStatusEnum $status, int $userId, int $page = 1, int $perPage = 10): Paginator
    {
        return $this->orders->getUserOrderByStatusPaginate($status,$userId,$page,$perPage);
    }

    public function cancelOrder(int $orderId): bool
    {
        return $this->orders->cancelOrder($orderId);
    }

    public function sendOrder(int $orderId): bool
    {
        return $this->orders->sendOrder($orderId);
    }

    public function updateOrder(CreateOrderRequest $orderRequest): Order
    {
        return $this->orders->updateOrder($orderRequest);
    }

    public function getOrderByUserSyncDataPaginate(User $user,?OrderStatusEnum $status, int $page = 1, int $perPage = 10): Paginator
    {
        return $this->orders->getOrderByUserSyncDataPaginate($user,$status,$page,$perPage);
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

    public function getTaskByUserSyncDataPaginate(User $user, ?OrderStatusEnum $status, int $page = 1, int $perPage = 10): Paginator
    {
        return $this->orders->getTaskByUserSyncDataPaginate($user,$status,$page,$perPage);
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

    public function updateTask(CreateTaskRequest $taskRequest): Task
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
}
