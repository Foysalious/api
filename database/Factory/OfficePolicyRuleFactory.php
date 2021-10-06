<?php namespace Factory;

use Sheba\Dal\OfficePolicyRule\OfficePolicyRule;

class OfficePolicyRuleFactory extends Factory
{
    protected function getModelClass()
    {
        return OfficePolicyRule::class;
    }

    protected function getData()
    {
        return [];
    }
    
}