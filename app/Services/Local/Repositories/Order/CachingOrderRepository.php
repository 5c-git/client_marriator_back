<?php

namespace App\Services\Local\Repositories\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Models\Order\Order;
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
}
