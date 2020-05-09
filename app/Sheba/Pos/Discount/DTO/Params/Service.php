<?php namespace Sheba\Pos\Discount\DTO\Params;

use App\Models\PartnerPosServiceDiscount;
use App\Models\PosOrderItem;

class Service extends SetParams
{
    /** @var PartnerPosServiceDiscount $discount */
    private $discount;
    private $amount;
    /** @var PosOrderItem $orderItem */
    private $orderItem;

    /**
     * @param PartnerPosServiceDiscount $discount
     * @return $this
     */
    public function setDiscount(PartnerPosServiceDiscount $discount)
    {
        $this->discount = $discount;
        return $this;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param PosOrderItem $order_item
     * @return $this
     */
    public function setPosOrderItem(PosOrderItem $order_item)
    {
        $this->orderItem = $order_item;
        return $this;
    }

    public function getBeforeData()
    {
        return [
            'discount_id' => $this->discount->id,
            'type' => $this->type,
            'amount' => $this->amount,
            'original_amount' => $this->discount->amount,
            'is_percentage' => $this->discount->is_amount_percentage,
            'cap' => $this->discount->cap,
        ];

    }

    public function getData()
    {
        return [
            'discount_id' => $this->discount->id,
            'type' => $this->type,
            'amount' => $this->amount,
            'original_amount' => $this->discount->amount,
            'is_percentage' => $this->discount->is_amount_percentage,
            'cap' => $this->discount->cap,
            'item_id' => $this->orderItem->id,
            'sheba_contribution' => 0.00,
            'partner_contribution' => 100.00
        ];
    }
}