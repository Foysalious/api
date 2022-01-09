<?php

namespace Database\Factories;

use App\Models\PartnerOrder;

class PartnerOrderFactory extends Factory
{
    protected $model = PartnerOrder::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'discount'           => 60,
            'sheba_collection'   => 0.00,
            'partner_collection' => 0.00,
            'refund_amount'      => 0.00,
            'is_reconciled'      => 0,
            'payment_method'     => 'cash-on-delivery',
        ]);
    }
}
