<?php

namespace Modules\Auth\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Http\Requests\ForgotPasswordRequest;

class ForgotPassword
{
    public function handle(ForgotPasswordRequest $request) {

        
        $request->user()->update([
            'password' => Hash::make($request->input('password'))
        ]);

        return $request->user();
    }
}
