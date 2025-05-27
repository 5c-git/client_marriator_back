<?php

namespace App\Services\Local\Repositories\Contracts;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Models\Order\Order;
use Illuminate\Contracts\Pagination\Paginator;
use App\Models\User;

interface OrderRepository
{
    public function createOrder(CreateOrderRequest $orderRequest,int $userId): Order;

    public function getUserOrderByStatus(?OrderStatusEnum $status, int $userId, int $page = 1, int $perPage = 10): Paginator;
    public function getOrderByUserSyncData(User $user,?OrderStatusEnum $status, int $page = 1, int $perPage = 10): Paginator;
    public function cancelOrder(int $orderId): bool;
    public function sendOrder(int $orderId): bool;

    public function updateOrder(CreateOrderRequest $orderRequest): Order;
    public function acceptedOrder(User $user,int $orderId): bool;
}
