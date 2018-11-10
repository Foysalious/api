<?php

namespace Sheba\TopUp\Vendor;

use App\Models\TopUpVendor;
use Sheba\TopUp\Vendor\Response\TopUpResponse;

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

    public function isPublished()
    {
        return $this->model->is_published;
    }

    abstract function recharge($mobile_number, $amount, $type): TopUpResponse;

    public function deductAmount($amount)
    {
        $this->model->amount -= $amount;
        $this->model->update();
    }

    public function refill($amount)
    {
        $this->model->amount += $amount;
        $this->model->update();
    }
}