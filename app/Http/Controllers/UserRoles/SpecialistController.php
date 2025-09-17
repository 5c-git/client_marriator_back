<?php

namespace App\Http\Controllers\UserRoles;

use App\Enum\Order\OrderStatusEnum;
use App\Enum\Order\ReportStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\AcceptBidRequest;
use App\Http\Requests\Order\GetBidRequest;
use App\Http\Requests\Order\GetBidsRequest;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\Order\BidResource;
use App\Http\Resources\Order\BidShortResource;
use App\Http\Resources\Order\OrderResource;
use App\Http\Resources\Order\ShortOrderResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\SuccessResource;
use App\Models\Order\Bid;
use App\Models\Order\Report;
use App\Models\User;
use App\Services\ApiTokenService\ApiTokenService;
use App\Services\Local\Repositories\Contracts\OrderRepository;
use App\Services\Local\Repositories\Contracts\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\Register\SmsCodeService;
use App\Http\Requests\Order\RejectBidRequest;
use App\Http\Requests\Order\StartDayRequest;
use App\Http\Requests\Order\EndDayRequest;
use App\Http\Requests\Order\ReportRequest;

class SpecialistController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected UserRepository $userRepository,protected OrderRepository $orderRepository)
    {
    }

    public function getBids(GetBidsRequest $request)
    {
        return BidShortResource::collection(
            $this->orderRepository->getBidsByUserSyncDataPaginate(
                $request->user(),
                $request->input('status') ? OrderStatusEnum::from($request->input('status')) : null
            )
        );
    }

    public function getBid(GetBidRequest $request)
    {
        return new BidResource(
            $this->orderRepository->getBidByUserSyncData(
                $request->user(),
                $request->input('bidId',null)
            )
        );
    }

    public function acceptBid(AcceptBidRequest $request): ErrorResource|SuccessResource
    {
        $user = $request->user();
        if($this->orderRepository->acceptBid($user,$request->bidId)) {
            return new SuccessResource();
        }else{
            return new ErrorResource();
        }
    }

    public function rejectBid(RejectBidRequest $request): ErrorResource|SuccessResource
    {
        $user = $request->user();
        if($this->orderRepository->rejectBid($user,$request->bidId)) {
            return new SuccessResource();
        }else{
            return new ErrorResource();
        }
    }

    public function startDay(StartDayRequest $request)
    {
        $user = $request->user();
        /** @var  $bid Bid */
        $bid = Bid::where('id',$request->bidId)->first();
        $report = new Report();
        $report->user_id = $user->id;
        $report->order_id = $bid->order_id;
        $report->task_id = $bid->task_id;
        $report->bid_id = $bid->id;
        $report->date_start = Carbon::now();
        $report->date_end = null;
        $report->status = ReportStatusEnum::start->value;
        $report->save();

        return new SuccessResource();
    }

    public function endDay(EndDayRequest $request)
    {
        $user = $request->user();
        /** @var  $report Report */
        $report = Report::query()
            ->where('user_id',$user->id)
            ->where('bid_id',$request->bidId)
            ->where('status',ReportStatusEnum::start->value)
            ->first();
        $report->status = ReportStatusEnum::end->value;
        $report->save();
        /** @var  $bid Bid */
        $bid = Bid::query()->where('id',$request->bidId)->first();
        if($bid->need_foto && $request->hasFile('reports')){
            foreach ($request->file('reports') as $reportFile){
                Storage::disk('public')->putFileAs('/source/reports/'.$reportFile->id.'-img', $reportFile, $reportFile->getClientOriginalName(),'public');
            }
        }

        return new SuccessResource();
    }
}
