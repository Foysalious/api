<?php

namespace Database\Factories;

use Sheba\Dal\TopUpVendorOTFChangeLog\Model as TopUpVendorOTFChangeLog;

class TopUpVendorOTFChangeLogFactory extends Factory
{
    protected $model = TopUpVendorOTFChangeLog::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'from_status' => 'Deactive',
            'to_status'   => 'Active',
            'log'         => 'OTF status changed from Deactive to Active.',
        ]);
    }
}
