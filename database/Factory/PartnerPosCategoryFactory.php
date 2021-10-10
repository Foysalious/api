<?php

/**
 * Khairun Nahar
 * pos Category factory for sDelivery Automation
 * May,2021
 */



namespace Factory;


use Sheba\Dal\PartnerPosCategory\PartnerPosCategory;

class PartnerPosCategoryFactory extends Factory
{
    protected function getModelClass()
    {
        return PartnerPosCategory::class;
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds,[
            'partner_id' => 1,
            'category_id' => 1,

        ]);
    }
}