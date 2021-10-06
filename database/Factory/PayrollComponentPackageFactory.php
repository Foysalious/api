<?php namespace Factory;

use Sheba\Dal\PayrollComponentPackage\PayrollComponentPackage;

class PayrollComponentPackageFactory extends Factory
{
    protected function getModelClass()
    {
        return PayrollComponentPackage::class;
    }

    protected function getData()
    {
        return [];
    }
}