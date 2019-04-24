<?php namespace Sheba\Pos\Order\RefundNatures;

use App\Models\PosOrder;

abstract class RefundNature
{
    /** @var PosOrder $order */
    public $order;

    public function setOrder(PosOrder $order)
    {
        $this->order = $order;
        return $this;
    }
}