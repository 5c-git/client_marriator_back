<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\BidAcceptingStatusEnum;
use App\Enum\Order\OrderStatusEnum;
use App\Enum\Role\RoleEnum;
use App\Http\Requests\FormRequest;
use App\Models\Fields\Directory\Radius;
use App\Models\Fields\Directory\TaxStatus;
use App\Models\Fields\Directory\ViewActivities;
use App\Models\Fields\Fields;
use App\Models\Order\Bid;
use App\Models\Order\Order;
use App\Models\Order\Task;
use App\Services\CoordinatesService;
use Illuminate\Validation\Rule;
use App\Models\User;

/**
 * @property-read int bidId
 */
class EntrustBidRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'bidId'         => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $user                = auth()->user();
                    $userIdsSupervisor   = $user->supervisors->pluck('id')->toArray();
                    $userIdsSupervisor[] = $user->id;
                    $taskExists          = Bid::query()
                        ->whereIn('user_id', $userIdsSupervisor)
                        ->where('id', $value)
                        ->where('status', OrderStatusEnum::new->value)
                        ->exists();

                    if (!$taskExists) {
                        $fail('Not your bid or status not new');
                    }
                },
            ],
            'specialistIds' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    /** @var  $bid Bid */
                    $bid      = Bid::query()->where('id', $this->bidId)->first();
                    $place    = $bid->place;
                    $status   = [];
                    $status[] = TaxStatus::query()->where('id', 1)->first()?->uuid;
                    if ($bid->self_employed) {
                        $status[] = TaxStatus::query()->where('id', 2)->first()?->uuid;
                    }
                    $fieldView = Fields::where('directory', ViewActivities::class)->first();
                    $fieldStat = Fields::where('directory', TaxStatus::class)->first();

                    if ($fieldView && $bid->viewActivity && $place) {
                        $users = User::whereJsonContains('data->' . $fieldView->uuid, $bid->viewActivity->uuid)
                            ->whereIn('data->' . $fieldStat->uuid, $status)
                            ->whereIn('id', $value)
                            ->whereDoesntHave('acceptedBids', function ($query) {
                                $query->where('accept_bid.accepted', BidAcceptingStatusEnum::notAccepted->value);
                            })
                            ->get();

                        $userInRadius = collect();
                        $radius       = Radius::where('default', true)->first();
                        if (!$radius) {
                            $radius = 5;
                        } else {
                            $radius = $radius->value;
                        }

                        foreach ($users as $user) {
                            if (
                                $user->latitude && $user->longitude && $place->latitude && $place->longitude &&
                                CoordinatesService::isPointInRadius(
                                    $user->latitude,
                                    $user->longitude,
                                    $place->latitude,
                                    $place->longitude,
                                    $user->mapRadius ?: $radius,
                                    $bid->radius ?: $radius
                                )) {
                                $userInRadius->push($user);
                            }
                        }
                    }

//                    $taskExists = Bid::query()
//                        ->where('id', $this->bidId)->first();
//                    if ($taskExists) {
//                        if ($taskExists->count > count($value)) {
//                            $fail('Supervisor count less than necessary need count');
//                        }
//                    }

                    $validIds = User::whereIn('id', $value)
                        ->whereHas('roles', fn($q) => $q->where('role_id', RoleEnum::specialist->value))
                        ?->pluck('id')
                        ->toArray();

                    if (!$validIds || count($validIds) < count($value) || $userInRadius->count() < count($value)) {
                        $fail('Supervisor ids not exist ' . implode(' ,', array_diff($value, $validIds)));
                    }
                }
            ],
        ];
    }
}
