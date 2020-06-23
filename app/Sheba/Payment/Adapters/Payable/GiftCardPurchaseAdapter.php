<?php namespace Sheba\Payment\Adapters\Payable;

use App\Models\Payable;
use Carbon\Carbon;

class GiftCardPurchaseAdapter implements PayableAdapter
{
    private $giftCardPurchaseOrder;
    private $emiMonth;

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
        $payable->emi_month = $this->resolveEmiMonth($payable);
        $payable->save();
        return $payable;
    }

    /**
     * @param $month |int
     * @return $this
     */
    public function setEmiMonth($month)
    {
        $this->emiMonth = (int)$month;
        return $this;
    }

    private function resolveEmiMonth(Payable $payable)
    {
        return $payable->amount >= config('sheba.min_order_amount_for_emi') ? $this->emiMonth : null;
    }

    public function canInit(): bool
    {
        return true;
    }
}