<?php

namespace App\Http\Controllers\UserRoles;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\GetOrderRequest;
use App\Http\Requests\Order\GetProjectForOrderRequest;
use App\Http\Requests\Order\OrderByIdCancelRequest;
use App\Http\Requests\SetBrandImgRequest;
use App\Http\Requests\SetPlaceRequest;
use App\Http\Resources\BrandResource;
use App\Http\Resources\Order\OneOrderResource;
use App\Http\Resources\Order\OrderResource;
use App\Http\Resources\Order\ShortOrderResource;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\SuccessResource;
use App\Models\Fields\Directory\ViewActivities;
use App\Models\Order\Order;
use App\Services\Local\Repositories\Contracts\OrderRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Order\OrderByIdRequest;
use App\Http\Resources\ErrorResource;
use App\Http\Requests\DelPlaceRequest;
use App\Http\Requests\SetUserDataRequest;
use App\Http\Resources\ViewActivityResource;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Requests\Order\CreateOrderActivityRequest;
use App\Http\Requests\Order\DeleteOrderActivityRequest;
use App\Http\Requests\Order\GetViewActivitiesForOrderRequest;
use App\Http\Requests\Order\RepeatOrderRequest;
use App\Http\Requests\Order\UpdateOrderActivityRequest;

class ClientController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected OrderRepository $orderRepository)
    {
    }

    public function getBrand()
    {
        $brands = Auth::user()->project->where('date_end','>=', Carbon::now())
            ->flatMap(fn($project) => $project->brands)
            ->unique('id');
        return BrandResource::collection($brands);
    }

    public function setBrandImg(SetBrandImgRequest $request)
    {
        $user = Auth::user();
        $brands = Auth::user()->project->where('date_end','>=', Carbon::now())
            ->flatMap(fn($project) => $project->brands)
            ->unique('id')?->where('id',$request->brandId)?->first();

        if(!empty($brands)){
            $user->img = $brands->logo;
            $user->save();
        }
        return new SuccessResource();
    }

    public function getPlace()
    {
        $places = Auth::user()->project->where('date_end','>=', Carbon::now())
            ->flatMap(fn($project) => $project->places)
            ->unique('id');
        return PlaceResource::collection($places);
    }

    public function getPlaceForOrder()
    {
        $places = Auth::user()->project->where('date_end','>=', Carbon::now())
            ->flatMap(fn($project) => $project->places)
            ->unique('id');
        $commonPlaces = $places->whereIn('id', Auth::user()->place->pluck('id'));

        return PlaceResource::collection($commonPlaces);
    }

    public function setPlace(SetPlaceRequest $request): SuccessResource
    {
        $user = Auth::user();
        $place = $user->project->where('date_end','>=', Carbon::now())
            ->flatMap(fn($project) => $project->places)
            ->unique('id')->whereIn('id',$request->placeId)->pluck('id')?->toArray();
        if(!empty($place)) {
            $user->place()->sync($place);
            $user->save();
        }
        return new SuccessResource();
    }

    public function delPlace(DelPlaceRequest $request): SuccessResource
    {
        $user = Auth::user();
        $user->place()->detach($request->placeId);
        return new SuccessResource();
    }

    public function setUserData(SetUserDataRequest $request): SuccessResource
    {
        $user = Auth::user();
        $user->name = $request->name;
        $user->save();
        return new SuccessResource();
    }

    public function createOrder(CreateOrderRequest $request): OrderResource
    {
        return new OrderResource(
            $this->orderRepository->createOrder(
                $request,
                Auth::user()->id
            )
        );
    }

    public function getProjectForOrder(GetProjectForOrderRequest $request)
    {
        $order = Order::query()->where('id',$request->orderId)->first();
        $projects = collect();
        if($order) {
            $user     = Auth::user();
            $projects = $user->project()
                ->whereHas('places', function ($query) use ($order) {
                    $query->where('directory_place.id', $order->place_id);
                })
                ->where('date_end', '>=', Carbon::now())
                ->where('self_employed', $order->self_employed)
                ->get();
        }
        return ProjectResource::collection($projects);
    }

    public function repeatOrder(RepeatOrderRequest $request): OrderResource
    {
        return new OrderResource(
            $this->orderRepository->repeatOrder(
                $request
            )
        );
    }

    public function createOrderActivity(CreateOrderActivityRequest $request)
    {
        return new OrderResource(
            $this->orderRepository->createOrderActivity(
                $request
            )
        );
    }

    public function updateOrderActivity(UpdateOrderActivityRequest $request)
    {
        return new OrderResource(
            $this->orderRepository->updateOrderActivity(
                $request
            )
        );
    }

    public function deleteOrderActivity(DeleteOrderActivityRequest $request)
    {
        return new OrderResource(
            $this->orderRepository->deleteOrderActivity(
                $request
            )
        );
    }

    public function updateOrder(UpdateOrderRequest $request): OrderResource
    {
        return new OrderResource($this->orderRepository->updateOrder($request));
    }

    public function getOrders(GetOrderRequest $request)
    {
        return OrderResource::collection(
            $this->orderRepository->getUserOrderByStatusPaginate(
                $request->status?OrderStatusEnum::from($request->status):null,
                Auth::user()->id,
            )
        );
    }

    public function getOrder(GetOrderRequest $request)
    {
        return new OneOrderResource(
            $this->orderRepository->getUserOrderByStatus(
                Auth::user()->id,
                $request->input('orderId',null)
            )
        );
    }

    public function cancelOrder(OrderByIdCancelRequest $request): ErrorResource|SuccessResource
    {
        return $this->orderRepository->cancelOrder($request->orderId) ?
            new SuccessResource() :
            new ErrorResource();
    }

    public function sendOrder(OrderByIdRequest $request)
    {
        return $this->orderRepository->sendOrder($request->orderId) ?
            new SuccessResource() :
            new ErrorResource();
    }

    public function getViewActivitiesForOrder(GetViewActivitiesForOrderRequest $request){
        $order = Order::where('id',$request->orderId)->first();
        $viewActivities = $order->place->project
            ->where('date_end', '>=', Carbon::now())
            ->flatMap(fn($project) => $project->viewActivities)
            ->unique('id');
        if(!$order->self_employed) {
            $viewActivities = $viewActivities->where('self_employed', false);
        }
        return ViewActivityResource::collection($viewActivities);
    }

}
