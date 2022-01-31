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
        return array_merge($this->commonSeeds, [
            'type'          => 'type',
            'from'          => 'accepted',
            'to'            => 'rejected',
            'log'           => 'Super Admin changed this leave status from Approved to Rejected',
        ]);
    }
}