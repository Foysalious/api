<?php

namespace Sheba\TopUp;

use App\Models\Affiliate;
use App\Models\TopUpOrder;
use Sheba\ModificationFields;
use DB;
use Sheba\TopUp\Vendor\Response\TopUpFailResponse;
use Sheba\TopUp\Vendor\Response\TopUpSuccessResponse;
use Sheba\TopUp\Vendor\Vendor;

class TopUp
{
    use ModificationFields;
    /** @var Vendor */
    private $vendor;
    /** @var \App\Models\TopUpVendor */
    private $model;
    /** @var TopUpAgent */
    private $agent;

    public function setAgent(TopUpAgent $agent)
    {
        $this->agent = $agent;
        return $this;
    }

    public function setVendor(Vendor $model)
    {
        $this->vendor = $model;
        $this->model = $this->vendor->getModel();
        return $this;
    }

    public function recharge($mobile_number, $amount, $type)
    {
        $mobile_number = formatMobile($mobile_number);
        $response = $this->vendor->recharge($mobile_number, $amount, $type);
        if ($response->hasSuccess()) {
            $response = $response->getSuccess();
            DB::transaction(function () use ($response, $mobile_number, $amount) {
                $this->placeTopUpOrder($response, $mobile_number, $amount);
                $amount_after_commission = $amount - $this->calculateCommission($amount);
                $this->agent->topUpTransaction($amount_after_commission, $amount . " has been topped up to " . $mobile_number);
                $this->vendor->deductAmount($amount);
            });
            return true;
        } else {
            return null;
        }
    }

    private function calculateCommission($amount)
    {
        return (double)$amount * ($this->model->agent_commission / 100);
    }

    private function placeTopUpOrder(TopUpSuccessResponse $response, $mobile_number, $amount)
    {
        $topUpOrder = new TopUpOrder();
        $topUpOrder->agent_type = "App\\Models\\" . class_basename($this->agent);
        $topUpOrder->agent_id = $this->agent->id;
        $topUpOrder->payee_mobile = $mobile_number;
        $topUpOrder->amount = $amount;
        $topUpOrder->status = 'Successful';
        $topUpOrder->transaction_id = $response->transactionId;
        $topUpOrder->transaction_details = json_encode($response->transactionDetails);
        $topUpOrder->vendor_id = $this->model->id;
        $topUpOrder->sheba_commission = ($amount * $this->model->sheba_commission) / 100;
        $topUpOrder->agent_commission = ($amount * $this->model->agent_commission) / 100;
        $this->setModifier($this->agent);
        $this->withCreateModificationField($topUpOrder);
        $topUpOrder->save();
    }

    public function processFailedTopUp(TopUpOrder $topUpOrder, TopUpFailResponse $topUpFailResponse)
    {
        DB::transaction(function () use ($topUpOrder, $topUpFailResponse) {
            $topUpOrder->status = 'Failed';
            $topUpOrder->transaction_details = json_encode($topUpFailResponse);
            $this->setModifier($this->agent);
            $this->withUpdateModificationField($topUpOrder);
            $topUpOrder->update();
            $this->refund($topUpOrder);
        });
    }

    private function refund(TopUpOrder $topUpOrder)
    {
        $amount = $topUpOrder->amount;
        $amount_after_commission = $amount - $this->calculateCommission($amount);
        /** @var TopUpAgent $agent */
        $agent = $topUpOrder->agent;
        $agent->refund($amount_after_commission, "Your recharge TK $amount to $topUpOrder->payee_mobile has failed, TK $amount_after_commission is refunded in your account.");
        if ($topUpOrder->agent instanceof Affiliate) $this->sendRefundNotificationToAffiliate($topUpOrder);
    }

    private function sendRefundNotificationToAffiliate(TopUpOrder $topUpOrder)
    {
        try {
            notify()->affiliate($topUpOrder->agent)->send([
                "title" => "Your moneybag has been refunded",
                "link" => url("affiliate/" . $topUpOrder->agent->id),
                "type" => 'warning',
                "event_type" => 'App\Models\Affiliate',
                "event_id" => $topUpOrder->agent->id
            ]);
        } catch (\Throwable $e) {

        }
    }
}