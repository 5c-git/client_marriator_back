<?php

namespace App\Services\ApiTokenService;

use App\Traits\Passport\PassportToken;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiTokenService
{
    use PassportToken;

    private object $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function createToken(array $scopes = []){
        $this->delUserTokens();
        return $this->getBearerTokenByUser($this->user,config('passport.personal_access_client')['id'],$scopes,false);
    }

    private function delUserTokens(){
        $this->user->tokens->each(function ($token, $key) {
            $token->delete();
        });
    }

    public static function refreshToken($refreshToken){

        $response = Http::asForm()->post(config('app.url').'/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => config('passport.personal_access_client')['id'],
            'client_secret' => config('passport.personal_access_client')['secret'],
            'scope' => 'checkPin',
        ]);
        return $response->json();
    }

}
