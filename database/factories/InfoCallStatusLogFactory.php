<?php

namespace Database\Factories;

use Sheba\Dal\InfoCallStatusLogs\InfoCallStatusLog;

class InfoCallStatusLogFactory extends Factory
{
    protected $model = InfoCallStatusLog::class;

    public function definition(): array
    {
        return [
            'info_call_id'          => '1',
            'from'                  => 'Open',
            'to'                    => 'Rejected',
            'reject_reason_id'      => '1',
            'reject_reason_details' => 'Anything',
            'created_by'            => '1',
            'created_by_name'       => 'IT - Kazi Fahd Zakwan',
            'created_at'            => $this->now,
        ];
    }
}
