<?php


namespace Sheba\Payment\Adapters\Payable;

use App\Models\Payable;
use Carbon\Carbon;

class UtilityOrderAdapter
{
    private $utilityOrder;

    public function setUtilityOrder($order_id)
    {
        $this->utilityOrder = $this->getUtilityOrderInfo($order_id);
        return $this;
    }

    public function getPayable(): Payable
    {
        $payable = new Payable();
        $payable->type = 'utility_order';
        $payable->type_id = $this->utilityOrder->id;
        $payable->user_id = $this->utilityOrder->user_id;
        $payable->user_type = "App\\Models\\" . $this->utilityOrder->user_type;
        $payable->amount = $this->utilityOrder->price;
        $payable->completion_type = "utility_order";
        $payable->success_url = config('sheba.front_url') . '/utility-orders/' . $this->utilityOrder->id;
        $payable->created_at = Carbon::now();
        $payable->save();
        return $payable;
    }

    private function getUtilityOrderInfo($id)
    {
        return json_decode(json_encode(['id' => 1, 'price' => 100, 'user_type' => 'Customer', 'user_id' => 11]));
    }
}