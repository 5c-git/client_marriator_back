<?php

namespace App\Services\Register;

use Illuminate\Support\Facades\Redis;
use App\Services\Register\EmailService;

class EmailVerifiedService
{

    public int $code;
    private string $email;

    public int $codeTtl=0;

    public string $status = 'success';

    public function __construct(string $email, int $code = 0 )
    {
        $this->email = $email;
        if(empty($code)) {
            $this->code = 1111;//rand(1000, 9999);
        }else{
            $this->code = $code;
        }
    }

    public function createCode():array
    {
        if(Redis::exists($this->email)){
            $this->codeTtl = Redis::ttl($this->email);
            $this->status = 'error';
            return ['status'=>'exists','ttl'=>$this->codeTtl];
        }else{
            if(EmailService::sendCode($this->email,$this->code)) {
                Redis::set($this->email, $this->code, 'EX', 120);
                return ['status'=>'success','code'=>$this->code,'ttl'=>120];
            }else{
                $this->status = 'error';
                return ['status'=>'errorSend'];
            }
        }
    }

    public function checkCode():array
    {
        if(Redis::exists($this->email)){
            if(Redis::get($this->email) == $this->code){
                Redis::del($this->email);
                return ['status'=>'success'];
            }else{
                return ['status'=>'error'];
            }
        }else{
            return ['status'=>'notExists'];
        }
    }

}
