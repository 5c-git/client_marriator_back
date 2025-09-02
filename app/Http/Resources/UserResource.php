<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\RoleResource;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting;

/**
 * @mixin \App\Models\User
 */
class UserResource extends JsonResource
{
    protected array $settings = [];
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'logo' =>  $this->img ? Storage::url($this->img) : null,
            'project' => ProjectResource::collection($this->project),
            'place' => PlaceResource::collection($this->place),
            'roles' => RoleResource::collection($this->roles),
            'change_order' => $this->change_order ?? $this->getSettings('change_order'),
            'cancel_order' => $this->cancel_order ?? $this->getSettings('cancel_order'),
            'live_order' => $this->live_order ?? $this->getSettings('live_order'),
            'change_task' => $this->change_task ?? $this->getSettings('change_task'),
            'cancel_task' => $this->cancel_task ?? $this->getSettings('cancel_task'),
            'live_task' => $this->live_task ?? $this->getSettings('live_task'),
            'repeat_bid' => $this->repeat_bid ?? $this->getSettings('repeat_bid'),
            'leave_bid' => $this->leave_bid ?? $this->getSettings('leave_bid'),
            'refusal_task' => $this->refusal_task ?? $this->getSettings('refusal_task'),
            'waiting_task' => (int)($this->waiting_task ?? $this->getSettings('waiting_task')),
            'count_wait_bid' => (int)($this->count_wait_bid ?? $this->getSettings('count_wait_bid')),
            'time_answer_bid' => (int)($this->time_answer_bid ?? $this->getSettings('time_answer_bid')),
            'notification_start' => (int)($this->notification_start ?? $this->getSettings('notification_start')),
            'supervisors' => ShortUserResource::collection($this->supervisors),
            'manager' => ShortUserResource::collection($this->manager),
            'counterparty' => CounterpartyResource::collection($this->counterparty)
        ];
    }

    protected function getSettings(string $key): int|string|null
    {
        if(!$this->settings){
            $settingsKeyValue = [];
            $settings = Setting::get();
            if($settings){
                foreach ($settings as $setting) {
                    /** @var Setting $setting */
                    $settingsKeyValue[$setting->key] = is_numeric($setting->value) ? (int)$setting->value : $setting->value;
                }
            }
            $this->settings = $settingsKeyValue;
        }
        return $this->settings[$key] ?? null;
    }
}
