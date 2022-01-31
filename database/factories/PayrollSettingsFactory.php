<?php

namespace Database\Factories;

use Sheba\Dal\PayrollSetting\PayrollSetting;

class PayrollSettingsFactory extends Factory
{
    protected $model = PayrollSetting::class;
    public function definition()
    {
        // TODO: Implement definition() method.
        return array_merge($this->commonSeeds, [
            'business_id'                       => 1,
            'is_enable'                         => 0,
            'payment_schedule'                  => 'once_a_month',
            'show_tax_report_download_banner'   => 1,
        ]);
    }
}