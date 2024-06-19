<?php

namespace App\Services\Register;

use Illuminate\Support\Facades\Redis;
use App\Services\Register\SmsService;

class SmsCodeService
{

    public string $code;
    private string $phone;

    public int $codeTtl=0;

    public string $status = 'success';

    public function __construct(string $phone, string $code = '')
    {
        $this->phone = $phone;
        if(empty($code)) {
            $this->code = rand(1000, 9999);
        }
    }

    public function createCode():array
    {
        if(Redis::exists($this->phone)){
            $this->codeTtl = Redis::ttl($this->phone);
            return ['status'=>'exists','ttl'=>$this->codeTtl];
        }else{
            if(SmsService::sendCode($this->phone,$this->code)) {
                Redis::set($this->phone, $this->code, 'EX', 120);
                return ['status'=>'success'];
            }else{
                $this->status = 'error';
                return ['status'=>'errorSend'];
            }
        }
    }

    public function checkCode():array
    {
        if(Redis::exists($this->phone)){
            if(Redis::get($this->phone) == $this->code){
                Redis::del($this->phone);
                return ['status'=>'success'];
            }else{
                return ['status'=>'error'];
            }
        }else{
            return ['status'=>'notExists'];
        }
    }

}
