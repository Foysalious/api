<?php

namespace Database\Factories;

use Sheba\Dal\BusinessPushNotificationLogs\Model as BusinessPushNotificationLog;

class BusinessPushNotificationLogFactory extends Factory
{
    protected $model = BusinessPushNotificationLog::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'notification_title'  => 'Test notification',
            'notification_body'   => 'Test notification',
            'notification_target' => 'notice_list',
            'client_info_type'    => 'specific_coworkers',
            'device'              => 'mobile',
        ]);
    }
}
