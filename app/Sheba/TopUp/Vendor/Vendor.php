<?php

namespace Sheba\TopUp\Vendor;

use App\Models\TopUpVendor;

abstract class Vendor
{
    protected $model;

    public function setModel(TopUpVendor $model)
    {
        $this->model = $model;
        return $this;
    }

    public function getModel()
    {
        return $this->model;
    }

    abstract function recharge($mobile_number, $amount, $type): TopUpResponse;
}