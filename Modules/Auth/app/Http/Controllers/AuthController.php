<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Modules\Auth\Actions\RegisterUser;
use Modules\Auth\Http\Requests\CheckUserRequest;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\User\Models\User;

class AuthController extends Controller
{
    public function checkUser(CheckUserRequest $request)
    {
        $contact = $request->input('contact');

        $user = User::where('email', $contact)->orWhere('phone', $contact)->first();

        if ($user) {
            return response()->json('user exists', 200);
        }

        return response()->json('user not found', 404);
    }

    public function register(RegisterRequest $request)
    {
        $user = (new RegisterUser)->handle($request);

        $token = $user->createToken('x_web_token', expiresAt: now()->addDays(30))->plainTextToken;

        $encryptedToken = Crypt::encryptString($token);
        
        return response()->json([
            'token' => $encryptedToken,
        ])->withCookie(cookie(
            'x_web_token',
            $encryptedToken,
            60 * 24 * 30,
            '/',
            config('session.domain'),
            true,
            true
        ));
    }

    public function login(LoginRequest $request){
        $user = Auth::user();

        $token = $user->createToken('x_web_token', expiresAt: now()->addDays(30))->plainTextToken;

        $encryptedToken = Crypt::encryptString($token);
        
                return response()->json([
            'token' => $encryptedToken,
        ])->withCookie(cookie(
            'x_web_token',
            $encryptedToken,
            60 * 24 * 30,
            '/',
            config('session.domain'),
            true,
            true
        ));
    }
}
