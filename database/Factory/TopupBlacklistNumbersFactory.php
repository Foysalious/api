<?php namespace Factory ;


use Sheba\Dal\TopUpBlacklistNumber\TopUpBlacklistNumber;

class TopupBlacklistNumbersFactory extends Factory
{

    protected function getModelClass()
    {
        // TODO: Implement getModelClass() method.
        return TopUpBlacklistNumber::class;
    }

    protected function getData()
    {
        // TODO: Implement getData() method.

        return array_merge($this->commonSeeds, [
            'name'=>'Test',
            'mobile'=>'+8801678987656'
        ]);

    }
}