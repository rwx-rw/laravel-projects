<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotificationPreference extends Model
{
    protected $table = 'users__notification_preferences';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['user_id', 'preferences'];

    public function casts(){
        return [
            'preferences' => 'array',
        ];
    }
}
