<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Modules\Auth\Actions\CreateUserToken;
use Modules\Auth\Actions\ForgotPassword;
use Modules\Auth\Actions\RegisterUser;
use Modules\Auth\Http\Requests\CheckUserRequest;
use Modules\Auth\Http\Requests\ForgotPasswordRequest;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Base\Http\Controllers\ApiController;
use Modules\User\Models\User;
use Throwable;

class AuthController extends ApiController
{
    public function checkUser(CheckUserRequest $request)
    {
        $contact = $request->input('contact');

        $user = User::where('email', $contact)->orWhere('phone', $contact)->first();

        if ($user) {
            return $this->successResponse('user exists.');
        }

        return $this->errorResponse('user not exists.', code: 404);
    }

    public function register(RegisterRequest $request)
    {
        $user = (new RegisterUser)->handle($request);

        $token = (new CreateUserToken)->handle($user, 'x_web_token');

        return $this->successResponse(data: ['token' => $token])->withCookie(cookie(
            'x_web_token',
            $token,
            60 * 24 * 30,
            '/',
            config('session.domain'),
            true,
            true
        ));
    }

    public function login(LoginRequest $request)
    {
        $user = Auth::user();

        $token = (new CreateUserToken)->handle($user, 'x_web_token');

        return $this->successResponse(data: ['token' => $token])->withCookie(cookie(
            'x_web_token',
            $token,
            60 * 24 * 30,
            '/',
            config('session.domain'),
            true,
            true
        ));
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        try {
            (new ForgotPassword)->handle($request);
        } catch (Throwable $th) {
            return $this->errorResponse('failed to reset password',code: 500);
        }
        return $this->successResponse('Password updated successfully');
    }
}
