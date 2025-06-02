<?php

namespace App\Services\Local\Repositories\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\Order\ConvertTaskRequest;
use App\Http\Requests\Order\CreateOrderRequest;
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

    public function getUserOrderByStatus(?OrderStatusEnum $status, int $userId, int $page = 1, int $perPage = 10): Paginator
    {
        return $this->orders->getUserOrderByStatus($status,$userId,$page,$perPage);
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

    public function getOrderByUserSyncData(User $user,?OrderStatusEnum $status, int $page = 1, int $perPage = 10): Paginator
    {
        return $this->orders->getOrderByUserSyncData($user,$status,$page,$perPage);
    }

    public function acceptedOrder(User $user, int $orderId): bool
    {
        return $this->orders->acceptedOrder($user,$orderId);
    }

    public function convertTask(User $user, ConvertTaskRequest $request): Task
    {
        return $this->orders->convertTask($user,$request);
    }

    public function getTaskByUserSyncData(User $user, ?OrderStatusEnum $status, int $page = 1, int $perPage = 10): Paginator
    {
        return $this->orders->getTaskByUserSyncData($user,$status,$page,$perPage);
    }
}
