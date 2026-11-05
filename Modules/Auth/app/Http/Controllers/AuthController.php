<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Auth\Http\Requests\CheckUserRequest;
use Modules\User\Models\User;

class AuthController extends Controller
{
    public function checkUser(CheckUserRequest $request)
    {
        $contact = $request->input('contact');

        $user = User::where('email', $contact)->orWhere('phone', $contact)->first();

        if($user){
            return response()->json('user exists',200);
        }

        return response()->json('user not found',404);
    }
}
