<?php

namespace Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\User\Transformers\NotificationResource;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function unread(Request $request)
    {
        $user = $request->user();

        return NotificationResource::collection($user->unreadNotifications);
    }

    /**
     * Display a listing of the resource.
     */
    public function read(Request $request)
    {
        $user = $request->user();

        return NotificationResource::collection($user->readNotifications);
    }
}
