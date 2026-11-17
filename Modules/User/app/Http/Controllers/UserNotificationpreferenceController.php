<?php

namespace Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\User\Http\Requests\UpdateNotificationPreferenceRequest;
use Modules\User\Services\NotificationPereferenceService;
use Modules\User\Transformers\NotificationPreferenceResource;

class UserNotificationpreferenceController extends Controller
{
    public function __construct(private NotificationPereferenceService $notificationPereferenceService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return new NotificationPreferenceResource(
            $this->notificationPereferenceService->getPreferences($request->user())
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNotificationPreferenceRequest $request)
    {
        $data = $request->validated();

        $this->notificationPereferenceService->update($request->user(), $data['notification_type'], $data['channel'], $data['value']);

        return new NotificationPreferenceResource(
            $this->notificationPereferenceService->getPreferences($request->user())
        );
    }
}
