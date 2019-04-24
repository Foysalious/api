<?php namespace Sheba\Pos\Order\RefundNatures;

use App\Models\PosOrder;

abstract class RefundNature
{
    /** @var PosOrder $order */
    public $order;
    /** @var array $data*/
    public $data;

    public function setOrder(PosOrder $order)
    {
        $this->order = $order;
        return $this;
    }

    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }
}