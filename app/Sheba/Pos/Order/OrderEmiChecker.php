<?php namespace App\Sheba\Pos\Order;


use App\Models\PosOrder;

class OrderEmiChecker
{
    /** @var $pos_order PosOrder */
    protected $pos_order;

    /**
     * @param mixed $pos_order
     * @return OrderEmiChecker
     */
    public function setOrder(PosOrder $pos_order)
    {
        $this->pos_order = $pos_order;
        $this->pos_order->calculate();
        return $this;
    }

    /**
     * @return bool
     */
    public function isEmiValidForOrder(): bool
    {
       if (!$this->isOrderedItemsValidForEmi()) {
            return false;
        } else {
           return true;
       }
    }

    /**
     * @return bool
     */
    private function isOrderedItemsValidForEmi(): bool
    {
        $order_items = $this->pos_order->items;
        foreach ($order_items as $item)
        {
            if($item->unit_price < 5000 || $item->is_emi_applied == false ) {
                return false;
            }
        }
        return true;
    }

}