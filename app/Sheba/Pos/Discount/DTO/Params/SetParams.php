<?php namespace Sheba\Pos\Discount\DTO\Params;

use App\Models\PosOrder;

abstract class SetParams
{
    protected $type;
    /** @var PosOrder $order */
    protected $order;

    public abstract function getData();

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param PosOrder $order
     * @return $this
     */
    public function setOrder(PosOrder $order)
    {
        $this->order = $order->isCalculated ? $order : $order->calculate();
        return $this;
    }
}