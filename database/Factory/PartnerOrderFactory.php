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
            'id' => 1,
            'order_id' => 1,
            'discount' => 60,
            'sheba_collection' => 0.00,
            'partner_collection' => 0.00,
            'refund_amount' => 0.00,
            'is_reconciled' => 0,
            'partner_searched_count' => 1,
            'finance_collection' => 'cash-on-delivery',
            'created_by' => 17,
            'created_by_name' => 'Fieoze Ahmed',
            'updated_by' => 6,
            'updated_by_name' =>'IT - Hasan Hafiz Pasha'
            ]);
    }
}