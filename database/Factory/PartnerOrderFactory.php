<?php namespace Factory;
use App\Models\PartnerOrder;

class PartnerOrderFactory extends Factory
{

    protected function getModelClass()
    {
        return PartnerOrder::class;// TODO: Implement getModelClass() method.
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'discount' => 60,
            'sheba_collection' => 0.00,
            'partner_collection' => 0.00,
            'refund_amount' => 0.00,
            'is_reconciled' => 0,
            'payment_method'=>'cash-on-delivery'
            ]);
    }
}