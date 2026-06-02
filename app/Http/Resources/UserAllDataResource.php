<?php

namespace App\Http\Resources;

use App\Http\Resources\PlaceResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\RoleResource;
use App\Models\Fields\Directory\Age;
use App\Models\Fields\Directory\Citizenship;
use App\Models\Fields\Directory\ViewActivities;
use App\Models\Fields\Fields;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting;

/**
 * @mixin \App\Models\User
 */
class UserAllDataResource extends JsonResource
{
    private array $moreInfo = [];
    private array $moreInfoField = [];
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
        $this->getMoreInformation();
        return [
            'id' => $this->id,
            'name' => $this->getName(),
            'phone' => $this->phone,
            'email' => $this->email,
            'logo' =>  $this->img ? Storage::url($this->img) : null,
            'project' => ProjectResource::collection($this->project->where('date_end','>=',Carbon::now())),
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
            'userManager' => ShortUserResource::collection($this->managersAsSpecialist),
            'userSupervisors' => ShortUserResource::collection($this->supervisorsAsSpecialist),
            'counterparty' => CounterpartyResource::collection($this->counterparty),
            'confirmRegister' => (bool)$this->confirmRegister,
            'finishRegister' => (bool)$this->finishRegister,
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

    private function getMoreInformation()
    {
        if(!$this->moreInfo) {
            $this->moreInfo['name']  = Fields::where('name', 'Имя')->first();
            $this->moreInfo['lastName']  = Fields::where('name', 'Фамилия')->first();
            $this->moreInfo['secondName']  = Fields::where('name', 'Отчество')->first();
        }
        if(!is_array($this->data)){
            $this->data = json_decode($this->data,true);
        }
    }

    private function getFieldView($name)
    {
        $data = '';
        if(!empty($this->data[$this->moreInfo[$name]->uuid])) {
            if (is_array($this->data[$this->moreInfo[$name]->uuid])) {
                $data = [];
                foreach ($this->data[$this->moreInfo[$name]->uuid] as $field) {
                    if (!empty($this->moreInfoField[$name][$field]['name'])) {
                        $data[] = $this->moreInfoField[$name][$field]['name'];
                    } else {
                        $data[] = $field;
                    }
                }
            } else {
                if (!empty($this->moreInfoField[$name][$this->data[$this->moreInfo[$name]->uuid]]['name'])) {
                    $data = $this->moreInfoField[$name][$this->data[$this->moreInfo[$name]->uuid]]['name'];
                } else {
                    $data = $this->data[$this->moreInfo[$name]->uuid];
                }
            }
        }
        return $data;
    }

    private function getName()
    {
        $name = trim($this->getFieldView('lastName'). ' ' .$this->getFieldView('name'). ' ' .$this->getFieldView('secondName'));
        if(!$name){
            $name = $this->name;
        }
        return $name;
    }
}
