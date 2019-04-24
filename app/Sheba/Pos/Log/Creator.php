<?php namespace Sheba\Pos\Log;

use App\Models\PosOrder;

class Creator
{
    /** @var PosOrder $order*/
    private $order;

    public function setOrder(PosOrder $order)
    {
        $this->order = $order;
        return $this;
    }
}