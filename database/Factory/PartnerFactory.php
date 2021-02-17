<?php


namespace Factory;


use App\Models\Partner;

class PartnerFactory extends Factory
{

    protected function getModelClass()
    {
        // TODO: Implement getModelClass() method.
        return Partner::class;
    }

    protected function getData()
    {
        // TODO: Implement getData() method.

        return array_merge($this->commonSeeds, [
            'name'=>'Khairun Nahar',
            'sub_domain'=>'test-shop',
            'logo'=>"https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/partners/logos/1572954290_sk_food.png",
            'wallet'=>10000

        ]);
    }
}