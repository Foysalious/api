<?php namespace Sheba\RewardShop;

use App\Models\RewardShopOrder;
use App\Models\RewardShopProduct;

class OrderValidator
{
    private $statuses;

    public function __construct()
    {
        $this->statuses = new OrderStatus();
    }

    public function canChangeStatus(RewardShopOrder $order, $requested_status)
    {
        if ($requested_status == $this->statuses->pending ||
            ($requested_status == $this->statuses->process && $order->status != $this->statuses->pending) ||
            ($requested_status == $this->statuses->served && $order->status != $this->statuses->process)) return false;
        return true;
    }

    public function canPurchase(RewardShopProduct $product, $user)
    {
        return ($user->reward_point >= $product->point);
    }
}