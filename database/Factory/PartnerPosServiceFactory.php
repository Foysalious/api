<?php


namespace Factory;


use App\Models\PartnerPosService;

class PartnerPosServiceFactory extends Factory
{
    protected function getModelClass()
    {
        return PartnerPosService::class;
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds,[
            'partner_id' => 1,
            'pos_category_id' => 1,
            'name'=>'Food',
            'publication_status'=>1,
            'is_published_for_shop'=>1,
            'price'=>100,
            'wholesale_price'=>80,
            'stock'=>20,
            'unit'=>'kg',
            'weight'=>5,
            'weight_unit'=>'kg',
        ]);
    }
}