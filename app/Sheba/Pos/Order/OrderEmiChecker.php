<?php namespace App\Sheba\Pos\Order;


use App\Models\PosOrder;

class OrderEmiChecker
{
    /** @var $pos_order PosOrder */
    protected $pos_order;

    /**
     * @param mixed $pos_order
     */
    public function setOrder(PosOrder $pos_order)
    {
        $this->pos_order = $pos_order;
        $this->pos_order->calculate();
        return $this;
    }

    public function isEmiValidForOrder()
    {
       if (!$this->isOrderedItemsValidForEmi()) {
            return false;
        }
        return true;
    }

    private function isOrderedItemsValidForEmi()
    {
        $order_items = $this->pos_order->items;
        foreach ($order_items as $item)
        {
            if($item->unit_price < 5000) {
                return false;
            } elseif ($item->is_emi_applied == false){
                return false;
            }
        }
        return true;
    }

}