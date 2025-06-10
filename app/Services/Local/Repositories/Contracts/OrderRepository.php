<?php

namespace App\Services\Local\Repositories\Contracts;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\Order\ConvertTaskRequest;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Models\Order\Order;
use Illuminate\Contracts\Pagination\Paginator;
use App\Models\User;
use App\Models\Order\Task;
use App\Http\Requests\Order\CreateTaskRequest;
use App\Models\Order\Bid;

interface OrderRepository
{
    public function createOrder(CreateOrderRequest $orderRequest,int $userId): Order;
    public function createTask(CreateTaskRequest $taskRequest,int $userId): Task;

    public function getUserOrderByStatusPaginate(?OrderStatusEnum $status, int $userId, int $page = 1, int $perPage = 10): Paginator;
    public function getUserOrderByStatus(int $userId,int|null $orderId): Order|null;
    public function getOrderByUserSyncDataPaginate(User $user,?OrderStatusEnum $status, int $page = 1, int $perPage = 10): Paginator;
    public function getOrderByUserSyncData(User $user,int|null $orderId): Order|null;
    public function cancelOrder(int $orderId): bool;
    public function sendOrder(int $orderId): bool;

    public function updateOrder(CreateOrderRequest $orderRequest): Order;
    public function updateTask(CreateTaskRequest $taskRequest): Task;
    public function acceptedOrder(User $user,int $orderId): bool;
    public function convertTask(User $user,ConvertTaskRequest $request): Task;
    public function getTaskByUserSyncDataPaginate(User $user,?OrderStatusEnum $status, int $page = 1, int $perPage = 10): Paginator;
    public function getTaskByUserSyncData(User $user,int|null $taskId): Task|null;

    public function instructTask(int $taskId,?array $supervisorIds): bool;
    public function invoiceTask(int $taskId,?array $supervisorId): bool;
    public function cancelTask(int $taskId): bool;
    public function acceptTask(User $user,int $taskId): bool;
    public function createBidFromOrder(User $user,int $orderId,int $orderActivityId): Bid;
    public function createBidFromTask(User $user,int $taskId,int $taskActivityId): Bid;
}
