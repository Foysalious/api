<?php

namespace Database\Factories;

use Carbon\Carbon;
use Sheba\Dal\LeaveStatusChangeLog\Model as LeaveStatusChangeLog;

class LeaveStatusChangeLogFactory extends Factory
{
    protected $model = LeaveStatusChangeLog::class;

    public function definition()
    {
        // TODO: Implement definition() method.
        return array_merge([
            'from_status' => 'pending',
            'to_status' => 'accepted',
            'log' => 'Super Admin changed this leave status from Pending to Accepted',
            'created_by' => 1,
            'created_at' => Carbon::now(),
        ]);
    }
}