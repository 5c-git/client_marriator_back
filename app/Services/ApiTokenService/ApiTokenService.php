<?php

namespace App\Services\ApiTokenService;

use App\Traits\Passport\PassportToken;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

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
            'scope' => 'personalArea',
        ]);

        $dataToken = $response->json();
        $user = null;

        if(!empty($dataToken) && !empty($dataToken['access_token'])){
            $token = $dataToken['access_token'];
            $token_parts = explode('.', $token);
            $token_header = $token_parts[1];
            $token_header_json = base64_decode($token_header);
            $token_header_array = json_decode($token_header_json, true);
            $token_id = $token_header_array['jti'];

            $user = DB::table('oauth_access_tokens')->where('id', $token_id)->first();
            if(!empty($user) && !empty($user->user_id)){
                $user = User::find($user->user_id);
                $dataToken = (new self($user))->createToken(['checkPin']);
            }
        }

        return [$dataToken,$user];
    }

}
