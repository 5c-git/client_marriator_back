<?php

namespace App\Services\User;

use App\Models\Fields\Fields;
use App\Models\User;

class UserDataService
{
    private array $moreInfo = [];
    private array $moreInfoField = [];
    protected array $settings = [];
    private User $user;

    public function __construct()
    {
        $this->getMoreInformation();
    }

    private function getMoreInformation(): void
    {
        if(!$this->moreInfo) {
            $this->moreInfo['name']  = Fields::where('name', 'Имя')->first();
            $this->moreInfo['lastName']  = Fields::where('name', 'Фамилия')->first();
            $this->moreInfo['secondName']  = Fields::where('name', 'Отчество')->first();
        }
    }

    private function getFieldView($name)
    {
        $data = '';
        if(!empty($this->user->data[$this->moreInfo[$name]->uuid])) {
            if (is_array($this->user->data[$this->moreInfo[$name]->uuid])) {
                $data = [];
                foreach ($this->user->data[$this->moreInfo[$name]->uuid] as $field) {
                    if (!empty($this->moreInfoField[$name][$field]['name'])) {
                        $data[] = $this->moreInfoField[$name][$field]['name'];
                    } else {
                        $data[] = $field;
                    }
                }
            } else {
                if (!empty($this->moreInfoField[$name][$this->user->data[$this->moreInfo[$name]->uuid]]['name'])) {
                    $data = $this->moreInfoField[$name][$this->user->data[$this->moreInfo[$name]->uuid]]['name'];
                } else {
                    $data = $this->user->data[$this->moreInfo[$name]->uuid];
                }
            }
        }
        return $data;
    }

    public function getName(User $user): string
    {
        $this->user = $user;
        if(!is_array($this->user->data)){
            $this->user->data = json_decode($this->user->data,true);
        }
        $name = trim($this->getFieldView('name') . ' ' .$this->getFieldView('lastName'). ' ' .$this->getFieldView('secondName'));
        if(!$name){
            $name = $this->user->name;
        }
        return $name??'';
    }
}
