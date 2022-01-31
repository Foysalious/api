<?php

namespace Database\Factories;

use App\Models\Notification;
use Carbon\Carbon;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;
    public function definition()
    {
        // TODO: Implement definition() method.
        return array_merge([
            'notifiable_type'       => 'App\Models\Member',
            'notifiable_id'         => 1,
            'title'                 => 'Test notification',
            'event_type'            => 'Sheba\Dal\Announcement\Announcement',
            'event_id'              => 1,
            'type'                  => 'Warning',
            'is_seen'               => 0,
            'created_at'            => Carbon::now(),
        ]);
    }
}