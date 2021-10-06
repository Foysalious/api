<?php namespace Factory;

use Sheba\Dal\ComponentPackageTarget\ComponentPackageTarget;

class ComponentPackageTargetFactory extends Factory
{
    protected function getModelClass()
    {
        return ComponentPackageTarget::class;
    }

    protected function getData()
    {
        return [];
    }
}