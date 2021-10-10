<?php namespace Sheba\AutoSpAssign\Sorting\Parameter;

use App\Models\Partner;

class Commission extends Parameter
{

    protected function getWeight()
    {
        return config('auto_sp.weights.quality.commission');
    }

    protected function getValueForPartner()
    {
        //will be moved to app/Sheba/AutoSpAssign/Finder.php
        return Partner::find($this->partner->getId())->categoryCommission($this->categoryId);
    }
}
