<?php namespace Sheba\Payment\Adapters\Payable;


use App\Models\Payable;

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
        $payable->amount = (double)$this->partnerOrder->due;
        $payable->completion_type = $this->isAdvancedPayment ? 'advanced_order' : "order";
        $payable->success_url = config('sheba.front_url') . '/orders/' . $this->partnerOrder->jobs()->where('status', '<>', constants('JOB_STATUSES')['Cancelled'])->first()->id;
        $payable->created_at = Carbon::now();
        $payable->save();

        return $payable;
    }
}