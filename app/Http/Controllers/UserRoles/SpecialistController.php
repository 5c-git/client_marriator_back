<?php

namespace App\Http\Controllers\UserRoles;

use App\Enum\Document\DocumentStatusEnum;
use App\Enum\Document\DocumentStatusSignatureEnum;
use App\Enum\Order\BidAcceptingStatusEnum;
use App\Enum\Order\OrderStatusEnum;
use App\Enum\Order\ReportStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Document\GetSignetDocumentRequest;
use App\Http\Requests\Document\SignedDocumentsSendCodeRequest;
use App\Http\Requests\Order\AcceptBidRequest;
use App\Http\Requests\Order\EndJobRequest;
use App\Http\Requests\Order\GetBidRequest;
use App\Http\Requests\Order\GetBidsRequest;
use App\Http\Requests\Order\GetJobRequest;
use App\Http\Requests\Order\PaySpecialistReportRequest;
use App\Http\Requests\UserData\SignContractRequest;
use App\Http\Resources\CounterpartyResource;
use App\Http\Resources\Document\DocumentResource;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\Order\BidResource;
use App\Http\Resources\Order\BidShortResource;
use App\Http\Resources\Order\JobResource;
use App\Http\Resources\Order\OrderResource;
use App\Http\Resources\Order\ShortOrderResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\SuccessResource;
use App\Models\Document\Document;
use App\Models\Fields\Directory\Counterparty;
use App\Models\Order\Bid;
use App\Models\Order\Report;
use App\Models\User;
use App\Models\User\UserContractData;
use App\Services\ApiTokenService\ApiTokenService;
use App\Services\DocumentCreator\UserDocumentCreatorService;
use App\Services\Local\Repositories\Contracts\OrderRepository;
use App\Services\Local\Repositories\Contracts\UserRepository;
use App\Services\Nopaper\NopaperService;
use Carbon\Carbon;
use Faker\Core\Uuid;
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
    public function __construct(protected UserRepository $userRepository, protected OrderRepository $orderRepository)
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
                $request->input('bidId', null)
            )
        );
    }

    public function acceptBid(AcceptBidRequest $request): ErrorResource|SuccessResource
    {
        $user = $request->user();
        if ($this->orderRepository->acceptBid($user, $request->bidId)) {
            return new SuccessResource();
        } else {
            return new ErrorResource();
        }
    }

    public function rejectBid(RejectBidRequest $request): ErrorResource|SuccessResource
    {
        $user = $request->user();
        if ($this->orderRepository->rejectBid($user, $request->bidId)) {
            return new SuccessResource();
        } else {
            return new ErrorResource();
        }
    }

    public function startDay(StartDayRequest $request)
    {
        $user = $request->user();
        /** @var  $dateEnd Carbon */
        $dateEnd = $request->dateEnd;
        /** @var  $bid Bid */
        $bid                     = Bid::where('id', $request->bidId)->first();
        $report                  = new Report();
        $report->user_id         = $user->id;
        $report->order_id        = $bid->order_id;
        $report->task_id         = $bid->task_id;
        $report->bid_id          = $bid->id;
        $report->date_start      = Carbon::now();
        $report->date_end        = null;
        $report->date_auto_close = $dateEnd->addHours(12);
        $report->status          = ReportStatusEnum::start->value;
        $report->dayActivity     = $request->dayActivity;
        if ($bid->external_id) {
            $report->pvp = true;
        } else {
            $report->pvp = false;
        }
        $report->save();

        return new SuccessResource();
    }

    public function endDay(EndDayRequest $request)
    {
        $user = $request->user();
        /** @var  $report Report */
        $report           = Report::query()
            ->where('user_id', $user->id)
            ->where('bid_id', $request->bidId)
            ->where('status', ReportStatusEnum::start->value)
            ->first();
        $report->status   = ReportStatusEnum::end->value;
        $report->date_end = Carbon::now();
        $report->hours    = round($report->date_start->diffInSeconds($report->date_end) / 3600, 2);

        /** @var  $bid Bid */
        $bid = Bid::query()->where('id', $request->bidId)->first();
        if ($bid->need_foto && $request->hasFile('reports')) {
            $reportFiles = [];
            foreach ($request->file('reports') as $reportFile) {
                $path          = $reportFile->store(
                    'source/reports/' . $user->id . '/' . $report->id,
                    'public'
                );
                $reportFiles[] = Storage::url($path);
            }
            $report->report = $reportFiles;
        }
        $report->save();
        return new SuccessResource();
    }

    public function getJob(GetJobRequest $request)
    {
        return new JobResource($this->orderRepository->getJobByUser($request));
    }

    public function getJobs()
    {
        $user         = Auth::user();
        $bids         = $this->orderRepository->getJobsByUserSyncDataPaginate(
            $user,
            $user->id
        );
        $expandedBids = $bids->flatMap(function ($bid) use ($user) {
            return $bid->acceptingUsers->map(function ($acceptingUser) use ($bid, $user) {
                if ($acceptingUser->id === $user->id) {
                    $newBid                = clone $bid;
                    $newBid->acceptingUser = $acceptingUser;
                    return $newBid;
                }
            });
        })->filter();
        return JobResource::collection($expandedBids);
    }

    public function endJob(EndJobRequest $request)
    {
        $user = Auth::user();
        $bid  = Bid::where('id', $request->bidId)->first();
        $bid->acceptingUsers()->updateExistingPivot($user->id, [
            'accepted' => BidAcceptingStatusEnum::canceled->value,
        ]);
        return new SuccessResource();
    }

    public function payReport(PaySpecialistReportRequest $request)
    {
        $user           = $request->user();
        $report         = Report::where('id', $request->reportId)->first();
        $report->status = ReportStatusEnum::paid->value;
        $report->save();
        $document                   = new Document();
        $document->uuid             = Str::uuid();
        $document->user_id          = $user->id;
        $document->file_path        = 'source/userImg/92/0zBLRLjoayNBUuwy0uNf.jpeg';
        $document->file_name        = 'docForPay_' . Carbon::now()->format('d.m.Y H:i:s') . '.pdf';
        $document->status           = DocumentStatusEnum::Signed->value;
        $document->status_signature = DocumentStatusSignatureEnum::NoSend->value;
        $document->date_signature   = Carbon::now();
        $document->save();
        return new SuccessResource();
    }

    public function signedDocuments(Request $request): ErrorResource|SuccessResource
    {
        $user     = $request->user();
        $document = Document::query()
            ->where('user_id', $user->id)
            ->where('status', DocumentStatusEnum::Signed->value)
            ->where('status_signature', DocumentStatusSignatureEnum::NoSend->value)
            ->first();
        if ($document && (new NopaperService())->sendDocumentsToNopaper($user)) {
            return new SuccessResource();
        }else{
            $document = Document::query()
                ->where('user_id', $user->id)
                ->where('status', DocumentStatusEnum::Signed->value)
                ->where('status_signature', DocumentStatusSignatureEnum::Process->value)
                ->first();
            if($document){
                $dataSendCode = (new NopaperService())->retriesSms($user);
                if (!empty($dataSendCode['success'])) {
                    return new SuccessResource();
                }
            }
        }
        return new ErrorResource();
    }

    public function signedDocumentsSendCode(SignedDocumentsSendCodeRequest $request): ErrorResource|SuccessResource
    {
        $user         = $request->user();
        $dataSendCode = (new NopaperService())->confirmSms($user, $request->code);
        if (!empty($dataSendCode['success'])) {
            return new SuccessResource();
        }
        return new ErrorResource($dataSendCode['message']);
    }

    public function signedDocumentsRetriesSms(Request $request): SuccessResource|ErrorResource
    {
        $user         = $request->user();
        $dataSendCode = (new NopaperService())->retriesSms($user);
        if (!empty($dataSendCode['success'])) {
            return new SuccessResource();
        }
        return new ErrorResource($dataSendCode['message']);
    }

    public function getSignetDocument(GetSignetDocumentRequest $request): DocumentResource
    {
        $user = $request->user();
        /** @var  $document Document */
        $document       = Document::query()
            ->where('id', $request->documentId)
            ->where('user_id', $user->id)
            ->where('status', DocumentStatusEnum::Signed->value)
            ->where('status_signature', DocumentStatusSignatureEnum::Signed->value)
            ->first();
        $nopaperService = new NopaperService();
        $documentInfo   = $nopaperService->getDocumentInfo($document);
        return new DocumentResource($documentInfo);
    }

    public function getCounterpartiesForSign(Request $request)
    {
        $user = $request->user();
        $userData = UserContractData::query()
            ->where('user_id',$user->id)
            ->where('date_start','<=',Carbon::now())
            ->where('date_end','>=',Carbon::now())
            ->get();
        $counterpartiesIds = [];
        foreach ($userData as $user){
            $counterpartiesIds[] = $user->counterparty_id;
        }
        $counterparties = Counterparty::query()
            ->whereNotIn('id',$counterpartiesIds)
            ->get();
        return CounterpartyResource::collection($counterparties);
    }

    public function signContracts(SignContractRequest $request)
    {
        $user = $request->user();
        $counterparties = Counterparty::query()
            ->whereIn('id',$request->counterpartyIds)
            ->get();
        $documents = collect();
        $service = new UserDocumentCreatorService();
        foreach ($counterparties as $counterparty){
            $document = $service->createContract($user,$counterparty);
            if($document){
                $documents->push($document);
            }
        }
        return DocumentResource::collection($documents);
    }
}
