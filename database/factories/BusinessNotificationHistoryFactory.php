<?php

namespace Database\Factories;

use Sheba\Dal\BusinessNotificationHistory\BusinessNotificationHistory;

class BusinessNotificationHistoryFactory extends Factory
{
    protected $model = BusinessNotificationHistory::class;
    public function definition()
    {
        // TODO: Implement definition() method.
        return array_merge($this->commonSeeds, [
            'business_id'           => 1,
            'business_member_id'    => 1,
            'action'                => 'checkin',
            'status'                => 'pending',
        ]);
    }
}