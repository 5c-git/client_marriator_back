<?php

namespace App\Services\Local\Repositories\Contracts;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\Order\CancelRequestRequest;
use App\Http\Requests\Order\ConvertTaskRequest;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\CreateRequestFromBidRequest;
use App\Http\Requests\Order\CreateRequestFromTaskRequest;
use App\Http\Requests\Order\CreateTaskActivityRequest;
use App\Http\Requests\Order\DeleteOrderActivityRequest;
use App\Http\Requests\Order\DeleteTaskActivityRequest;
use App\Http\Requests\Order\GetJobRequest;
use App\Http\Requests\Order\UpdateOrderActivityRequest;
use App\Http\Requests\Order\UpdateTaskActivityRequest;
use App\Http\Requests\Order\UpdateTaskRequest;
use App\Models\Order\Order;
use App\Models\Order\Request;
use App\Models\Order\SearchRequest;
use App\Models\Order\TaskActivity;
use FontLib\Table\Type\post;
use Illuminate\Contracts\Pagination\Paginator;
use App\Models\User;
use App\Models\Order\Task;
use App\Http\Requests\Order\CreateTaskRequest;
use App\Models\Order\Bid;
use Illuminate\Support\Collection;
use App\Http\Requests\Order\BidDataRequest;
use App\Http\Requests\Order\CreateOrderActivityRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Requests\Order\RepeatTaskRequest;
use App\Http\Requests\Order\RepeatOrderRequest;

interface OrderRepository
{
    public function createOrder(CreateOrderRequest $orderRequest,int $userId): Order;
    public function createOrderActivity(CreateOrderActivityRequest $request): Order;
    public function createTask(CreateTaskRequest $taskRequest,int $userId): Task;
    public function createTaskActivity(CreateTaskActivityRequest $taskRequest): Task;
    public function getUserOrderByStatusPaginate(?OrderStatusEnum $status, int $userId): Collection;
    public function getUserOrderByStatus(int $userId,int|null $orderId): Order|null;
    public function getOrderByUserSyncDataPaginate(User $user,?OrderStatusEnum $status): Collection;
    public function getOrderByUserSyncData(User $user,int|null $orderId): Order|null;
    public function cancelOrder(int $orderId): bool;
    public function sendOrder(int $orderId): bool;

    public function updateOrder(UpdateOrderRequest $orderRequest): Order;
    public function deleteOrderActivity(DeleteOrderActivityRequest $orderRequest): Order;
    public function deleteTaskActivity(DeleteTaskActivityRequest $taskRequest): Task;
    public function updateTask(UpdateTaskRequest $taskRequest): Task;
    public function acceptedOrder(User $user,int $orderId): bool;
    public function convertTask(User $user,ConvertTaskRequest $request): Task;
    public function getTaskByUserSyncDataPaginate(User $user,?OrderStatusEnum $status): Collection;
    public function getTaskByUserSyncData(User $user,int|null $taskId): Task|null;
    public function instructTask(int $taskId,?array $supervisorIds): bool;
    public function invoiceTask(int $taskId,?array $supervisorId): bool;
    public function cancelTask(int $taskId): bool;
    public function acceptTask(User $user,int $taskId): bool;
    public function createBidFromOrder(User $user,int $orderId,int $orderActivityId): Bid;
    public function createSearchFromOrder(User $user,int $orderId,int $orderActivityId): SearchRequest;
    public function createBidFromTask(User $user,int $taskId,int $taskActivityId): Bid;
    public function createSearchFromTask(User $user,int $taskId,int $taskActivityId): SearchRequest;
    public function getBidsByUserSyncDataPaginate(User $user,?OrderStatusEnum $status): Collection;
    public function getJobsByUserSyncDataPaginate(User $user,$specialistId = null): Collection;
    public function getJobByUser(GetJobRequest $request): Bid;
    public function getBidByUserSyncData(User $user,int|null $bidId): Bid|null;
    public function invoiceBid(int $bidId,?array $specialistIds): bool;
    public function acceptBid(User $user,int $bidId):bool;
    public function rejectBid(User $user,int $bidId):bool;
    public function instructBid(int $bidId,int $specialistId):bool;
    public function cancelBid(int $bidId): bool;
    public function getSpecialistForBid(int $bidId): Collection;
    public function updateBid(BidDataRequest $bidRequest): Bid;
    public function createRequestFromTask(CreateRequestFromTaskRequest $request, User $user): Request;
    public function createRequestFromBid(CreateRequestFromBidRequest $request, User $user): Request;
    public function cancelRequest(CancelRequestRequest $request): bool;
    public function repeatTask(RepeatTaskRequest $request): Task;
    public function repeatOrder(RepeatOrderRequest $request): Order;
    public function updateTaskActivity(UpdateTaskActivityRequest $request): Task;
    public function updateOrderActivity(UpdateOrderActivityRequest $request): Order;

}
