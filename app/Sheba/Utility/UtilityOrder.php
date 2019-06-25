<?php namespace Sheba\Utility;

use App\Models\Payable;
use Sheba\Payment\PayableType;

class UtilityOrder implements PayableType
{
    private $payable;
    private $id;

    public function __get($name)
    {
        return $this->$name;
    }

    public function setPayable(Payable $payable)
    {
        $this->payable = $payable;
        $this->initialize();
        return $this;
    }

    private function initialize()
    {
        $this->id = $this->payable->type_id;
    }

}