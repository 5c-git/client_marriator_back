<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/cabinet/account';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function customAdminLogin(Request $request)
    {
        $data = $request->all();

        $user = User::where('email', $data['email'])->first();

        if ($user && $user->isAdmin()) {
            $response = Auth::attempt(['email' => $data['email'], 'password' => $data['password']], true);
            if ($response) {
                return 'success';
            }

        }

        return false;

    }

    public function logout()
    {
        Session::flush();
        Auth::logout();
        return redirect(route('adminLogin'));
    }

}
