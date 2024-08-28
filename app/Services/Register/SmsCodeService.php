<?php

namespace App\Services\Register;

use Illuminate\Support\Facades\Redis;
use App\Services\Register\SmsService;

class SmsCodeService
{

    public int $code;
    private string $phone;

    public int $codeTtl=0;

    public string $status = 'success';

    public function __construct(string $phone, int $code = 0 )
    {
        $this->phone = $phone;
        if(empty($code)) {
            $this->code = 1111;//rand(1000, 9999);
        }else{
            $this->code = $code;
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
                return ['status'=>'success','code'=>$this->code,'ttl'=>120];
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
