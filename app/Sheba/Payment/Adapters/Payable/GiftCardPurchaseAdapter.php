<?php namespace Sheba\Payment\Adapters\Payable;

use App\Models\Payable;
use Carbon\Carbon;

class GiftCardPurchaseAdapter implements PayableAdapter
{
    private $giftCardPurchaseOrder;

    public function setModelForPayable($model)
    {
        $this->giftCardPurchaseOrder = $model;
        return $this;
    }

    public function getPayable(): Payable
    {
        $payable = new Payable();
        $payable->type = 'gift_card_purchase';
        $payable->type_id = $this->giftCardPurchaseOrder->id;
        $payable->user_id = $this->giftCardPurchaseOrder->customer_id;
        $payable->user_type = "App\\Models\\Customer";
        $payable->amount = $this->giftCardPurchaseOrder->amount;
        $payable->completion_type = "gift_card_purchase";
        $payable->success_url = config('sheba.front_url') . '/profile/credit';
        $payable->created_at = Carbon::now();
        $payable->save();
        return $payable;
    }

    public function setEmiMonth($month)
    {
        // TODO: Implement setEmiMonth() method.
    }
}