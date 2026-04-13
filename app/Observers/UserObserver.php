<?php

namespace App\Observers;

use App\Models\User;
use App\Models\User\UserUpdates;

class UserObserver
{

    private array $userFieldForCheckUpdate = [
        'name',
        'email',
        'phone',
        'data',
        'img',
        'pin',
        'estateData',
        'requisitesData',
        'mapAddress',
        'mapRadius',
        'change_order',
        'cancel_order',
        'live_order',
        'change_task',
        'cancel_task',
        'live_task',
        'repeat_bid',
        'leave_bid',
        'refusal_task',
        'waiting_task',
        'latitude',
        'longitude',
        'count_wait_bid',
        'time_answer_bid',
        'notification_start',
        'change_fields'
    ];

    private array $userFieldJson = [
        'data'=>'dataFormater',
        'estateData' =>'estateDataFormater',
        'requisitesData' => 'requisitesDataFormater'
    ];
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {

    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $newUser = $user->toArray();
        $originalData = $user->getOriginal();
        if(isset($newUser['finishRegister']) && isset($newUser['confirmRegister']) && $newUser['finishRegister'] == true && $newUser['confirmRegister'] == true) {
            $data = [];
            foreach ($this->userFieldForCheckUpdate as $field) {
                if ($newUser[$field] != $originalData[$field]) {
                    if (!empty($this->userFieldJson[$field])) {
                        $func = $this->userFieldJson[$field];
                        if (empty($newUser[$field])) {
                            $newUser[$field] = [];
                        } else {
                            $newUser[$field] = json_decode($newUser[$field], true);
                        }

                        if (empty($originalData[$field])) {
                            $originalData[$field] = [];
                        } else {
                            $originalData[$field] = json_decode($originalData[$field], true);
                        }
                        $data = array_merge($this->$func($originalData[$field], $newUser[$field]), $data);
                    } else {
                        $data[] = [
                            'key' => $field,
                            'new' => $newUser[$field],
                            'old' => $originalData[$field],
                        ];
                    }
                }
            }

            if ($data) {
                $this->saveDataUpdates($data, $user);
            }
        }
    }

    private function dataFormater(array $oldData,array $newData): array
    {
        $dataForSave = [];
        foreach ($oldData as $t=>$oldArr) {
            if(!isset($newData[$t])){
                $newData[$t] = [];
            }
            if(is_array($newData[$t])) {
                asort($newData[$t]);
            }
            if(is_array($oldArr)) {
                asort($oldArr);
            }
            if($newData[$t] != $oldArr){
                echo "<pre>";
                var_dump([
                    'key' => $t,
                    'new' => is_array($newData[$t])?json_encode($newData[$t] ?? []):$newData[$t],
                    'old' => is_array($oldArr)?json_encode($oldArr ?? []):$oldArr,
                ]);
                echo "</pre>";
                $dataForSave[] = [
                    'key' => $t,
                    'new' => is_array($newData[$t])?json_encode($newData[$t] ?? []):$newData[$t],
                    'old' => is_array($oldArr)?json_encode($oldArr ?? []):$oldArr,
                ];
            }
        }
        return $dataForSave;
    }

    private function array_diff_assoc_recursive($array1, $array2) {
        $difference = [];
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!isset($array2[$key]) || !is_array($array2[$key]) || $array2[$key] === $value) {
                    $difference[$key] = $value;
                } else {
                    $new_diff = $this->array_diff_assoc_recursive($value, $array2[$key]);
                    if (!empty($new_diff)) {
                        $difference[$key] = $new_diff;
                    }
                }
            }
        }
        return $difference;
    }

    private function estateDataFormater(array $oldData, array $newData): array
    {
        $dataForSave = [];
        foreach ($oldData as $k => $data) {
            if ($data != $newData[$k]) {
                $dataForSave[] = [
                    'key' => 'estateData',
                    'new' => json_encode($newData),
                    'old' => json_encode($oldData),
                ];
            }
        }
        return $dataForSave;
    }

    private function requisitesDataFormater(array $oldData, array $newData): array
    {
        $dataForSave = [];
        foreach ($oldData as $k => $data) {
            if ($data != $newData[$k]) {
                $dataForSave[] = [
                    'key' => 'requisitesData',
                    'new' => json_encode($newData),
                    'old' => json_encode($oldData),
                ];
            }
        }
        return $dataForSave;
    }

    private function saveDataUpdates(array $data, User $user){
        foreach ($data as $updates){
            $obj = new UserUpdates();
            $obj->user_id = $user->id;
            $obj->field = $updates['key'];
            $obj->newData = $updates['new'];
            $obj->oldData= $updates['old'];
            $obj->status= 1;
            $obj->save();
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $user->place()->detach();
        $user->project()->detach();
        $user->roles()->detach();
        $user->counterparty()->detach();
        //$user->reports()->detach();
        $user->acceptedBids()->detach();
        $user->acceptedTasks()->detach();
        $user->acceptedOrders()->detach();
        $user->supervisorSpecialist()->detach();
        $user->managerSpecialist()->detach();
        $user->manager()->detach();
        $user->supervisors()->detach();

    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {

    }

    /**
     * Handle the User "forceDeleted" event.
     */
    public function forceDeleted(User $user): void
    {
    }
}
