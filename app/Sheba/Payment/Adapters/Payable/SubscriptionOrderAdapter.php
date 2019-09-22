<?php namespace Sheba\Payment\Adapters\Payable;

use App\Models\Business;
use App\Models\Payable;
use App\Models\SubscriptionOrder;
use Carbon\Carbon;
use Sheba\Payment\PayableUser;

class SubscriptionOrderAdapter implements PayableAdapter
{
    /** @var SubscriptionOrder */
    private $subscriptionOrder;
    /** @var PayableUser */
    private $user;
    private $emiMonth;

    public function setModelForPayable($model)
    {
        $this->subscriptionOrder = $model;
        return $this;
    }

    public function setUser(PayableUser $user)
    {
        $this->user = $user;
        return $this;
    }

    public function getPayable(): Payable
    {
        $payable = new Payable();
        $payable->type = 'subscription_order';
        $payable->type_id = $this->subscriptionOrder->id;
        $payable->user_id = $this->user->id;
        $payable->user_type = get_class($this->user);
        $payable->amount = $this->subscriptionOrder->getTotalPrice();
        $payable->completion_type = "subscription_order";
        $payable->success_url = $this->resolveRedirectUrl();
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

    private function resolveRedirectUrl()
    {
        if ($this->user instanceof Business) {
            return config('sheba.business_url') . '/dashboard/subscriptions/' . $this->subscriptionOrder->id;
        } else {
            return config('sheba.front_url') . '/subscription-orders/' . $this->subscriptionOrder->id;
        }

    }
}