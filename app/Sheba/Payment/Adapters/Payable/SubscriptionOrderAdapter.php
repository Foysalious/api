<?php namespace Sheba\Payment\Adapters\Payable;


use App\Models\Payable;
use Carbon\Carbon;

class SubscriptionOrderAdapter implements PayableAdapter
{
    private $subscriptionOrder;

    public function setModelForPayable($model)
    {
        $this->subscriptionOrder = $model;
        return $this;
    }

    public function getPayable(): Payable
    {
        $payable = new Payable();
        $payable->type = 'subscription_order';
        $payable->type_id = $this->subscriptionOrder->id;
        $payable->user_id = $this->subscriptionOrder->customer_id;
        $payable->user_type = "App\\Models\\Customer";
        $payable->amount = (double)json_decode($this->subscriptionOrder->service_details)->discounted_price;
        $payable->completion_type = "order";
        $payable->success_url = config('sheba.front_url') . '/orders/' . $this->subscriptionOrder->id;
        $payable->created_at = Carbon::now();
        $payable->save();
        return $payable;
    }
}