<?php namespace Factory;

use Sheba\Dal\InfoCallStatusLogs\InfoCallStatusLog;

class InfoCallStatusLogFactory extends Factory
{
    protected function getModelClass()
    {
        return InfoCallStatusLog::class;
    }

    protected function getData()
    {
        return [
            'info_call_id' => '1',
            'from' => 'Open',
            'to' => 'Rejected',
            'reject_reason_id' => '1',
            'reject_reason_details' => 'Anything',
            'created_by' => '1',
            'created_by_name' => 'IT - Kazi Fahd Zakwan',
            'created_at' => $this->now
        ];
    }
}