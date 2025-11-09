<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;

class TimeService
{

    public static function getTimeDifferenceAdd(User $user,string $field,?Carbon $time): bool
    {//функция для сравнение что время с добавлением интервала больше текущего
        if(!$time){
            return false;
        }
        if(!empty($user->$field)){
            $defaultValue = $user->$field;
        }else{
            $defaultValue = Setting::getValue($field);
        }
        $timeToSubtract = Carbon::parse($defaultValue);
        $timeAfterPeriod = $time->addHours($timeToSubtract->hour)
            ->addMinutes($timeToSubtract->minute);

        return $timeAfterPeriod->gt(Carbon::now());
    }

    public static function getTimeDifferenceSub(User $user,string $field,?Carbon $time): bool
    {//функция для сравнение что время в будующем с отниманием интервала меньше текущего
        if(!$time){
            return false;
        }
        if(!empty($user->$field)){
            $defaultValue = $user->$field;
        }else{
            $defaultValue = Setting::getValue($field);
        }
        $timeToSubtract = Carbon::parse($defaultValue);
        $timeAfterPeriod = $time->subHours($timeToSubtract->hour)
            ->subMinutes($timeToSubtract->minute);

        return $timeAfterPeriod->lt(Carbon::now());
    }
}
