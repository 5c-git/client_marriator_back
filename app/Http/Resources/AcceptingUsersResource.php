<?php

namespace App\Http\Resources;

use App\Http\Resources\PlaceResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\RoleResource;
use App\Models\Fields\Directory\Age;
use App\Models\Fields\Directory\Citizenship;
use App\Models\Fields\Directory\TaxStatus;
use App\Models\Fields\Directory\ViewActivities;
use App\Models\Fields\Fields;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin \App\Models\User
 */
class AcceptingUsersResource extends JsonResource
{
    private array $moreInfo = [];
    private array $moreInfoField = [];
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
            'phone' => $this->phone,
            'email' => $this->email,
            'logo' =>  $this->img ? Storage::url($this->img) : null,
            'roles' => RoleResource::collection($this->roles),
            'radius' => $this->mapRadius,
            'status' => $this->pivot->accepted,
            'name' => $this->getName(),
            'age' => $this->getFieldView('fieldAge'),
            'country' => $this->getFieldView('fieldCiti'),
            'viewActivities' => $this->getFieldView('fieldView'),

        ];
    }

    private function getMoreInformation()
    {
        if(!$this->moreInfo) {
            $this->moreInfo['fieldView'] = Fields::where('directory', ViewActivities::class)->first();
            $this->moreInfo['fieldCiti'] = Fields::where('directory', Citizenship::class)->first();
            $this->moreInfo['fieldAge']  = Fields::where('directory', Age::class)->first();
            $this->moreInfo['name']  = Fields::where('name', 'Имя')->first();
            $this->moreInfo['lastName']  = Fields::where('name', 'Фамилия')->first();
            $this->moreInfo['secondName']  = Fields::where('name', 'Отчество')->first();

            $this->moreInfoField['fieldView']=ViewActivities::get()->keyBy('uuid')->toArray();
            $this->moreInfoField['fieldCiti']=Citizenship::get()->keyBy('uuid')->toArray();
            $this->moreInfoField['fieldAge']=Age::get()->keyBy('uuid')->toArray();
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
                foreach ($this->data[$this->moreInfo[$name]->uuid] as $field) {
                    if (!empty($this->moreInfoField[$name][$field]['name'])) {
                        if ($data) {
                            $data = $data . ', ' . $this->moreInfoField[$name][$field]['name'];
                        } else {
                            $data = $data . $this->moreInfoField[$name][$field]['name'];
                        }
                    } else {
                        if ($data) {
                            $data = $data . ', ' . $field;
                        } else {
                            $data = $data . $field;
                        }
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
        $name = trim($this->getFieldView('name') . ' ' .$this->getFieldView('lastName'). ' ' .$this->getFieldView('secondName'));
        if(!$name){
            $name = $this->name;
        }
        return $name;
    }
}
