<?php

namespace Database\Factories;

use Carbon\Carbon;
use Sheba\Dal\LeaveLog\Model as LeaveLog;

class LeaveLogFactory extends Factory
{
    protected $model = LeaveLog::class;

    public function definition()
    {
        // TODO: Implement definition() method.
        return array_merge([
            'type' => 'leave_update',
            'from' => 'pending',
            'to' => 'accepted',
            'log' => 'Super Admin changed this leave status from Pending to Accepted',
            'is_changed_by_super' => 0,
            'created_by' => 1,
            'created_at' => Carbon::now(),
        ]);
    }
}