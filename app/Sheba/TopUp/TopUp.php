<?php namespace Sheba\TopUp;

use App\Models\Affiliate;
use App\Models\TopUpOrder;
use App\Sheba\TopUp\Commission\CommissionFactory;
use Sheba\ModificationFields;
use DB;
use Sheba\TopUp\Vendor\Response\TopUpFailResponse;
use Sheba\TopUp\Vendor\Response\TopUpSuccessResponse;
use Sheba\TopUp\Vendor\Vendor;
use Sheba\TopUp\Vendor\VendorFactory;

class TopUp
{
    use ModificationFields;
    /** @var Vendor */
    private $vendor;
    /** @var \App\Models\TopUpVendor */
    private $model;
    /** @var TopUpAgent */
    private $agent;

    private $isSuccessful;

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
        if ($this->agent->wallet >= $amount) {
            $mobile_number = formatMobile($mobile_number);
            $response = $this->vendor->recharge($mobile_number, $amount, $type);
            if ($response->hasSuccess()) {
                $response = $response->getSuccess();
                DB::transaction(function () use ($response, $mobile_number, $amount) {
                    $this->placeTopUpOrder($response, $mobile_number, $amount);
                    $amount_after_commission = $amount - $this->agent->calculateCommission($amount, $this->model);
                    $this->agent->topUpTransaction($amount_after_commission, $amount . " has been topped up to " . $mobile_number);
                    $this->vendor->deductAmount($amount);
                    $this->isSuccessful = true;
                });
            }
        }
    }

    public function isNotSuccessful()
    {
        return !$this->isSuccessful;
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
        $topUpOrder->agent_commission = 0.00;

        $this->setModifier($this->agent);
        $this->withCreateModificationField($topUpOrder);
        $topUpOrder->save();

        $commission = new CommissionFactory();
        $commission->getByName($topUpOrder->agent_type)->setAgent($this->agent)->setTopUpOrder($topUpOrder)->setTopUpVendor($this->model)->disburse();
    }

    public function processFailedTopUp(TopUpOrder $topUpOrder, TopUpFailResponse $topUpFailResponse)
    {
        if ($topUpOrder->isFailed()) return true;
        DB::transaction(function () use ($topUpOrder, $topUpFailResponse) {
            $this->model = $topUpOrder->vendor;
            $topUpOrder->status = 'Failed';
            $topUpOrder->transaction_details = json_encode($topUpFailResponse->getFailedTransactionDetails());
            $this->setModifier($this->agent);
            $this->withUpdateModificationField($topUpOrder);
            $topUpOrder->update();
            $this->refund($topUpOrder);
            $vendor = new VendorFactory ();
            $vendor = $vendor->getById($topUpOrder->vendor_id);
            $vendor->refill($topUpOrder->amount);
        });
    }

    private function refund(TopUpOrder $topUpOrder)
    {
        $amount = $topUpOrder->amount;
        /** @var TopUpAgent $agent */
        $agent = $topUpOrder->agent;
        $amount_after_commission = round($amount - $agent->calculateCommission($amount, $this->model), 2);
        $log = "Your recharge TK $amount to $topUpOrder->payee_mobile has failed, TK $amount_after_commission is refunded in your account.";
        $agent->refund($amount_after_commission, $log);
        if ($topUpOrder->agent instanceof Affiliate) $this->sendRefundNotificationToAffiliate($topUpOrder, $log);
    }

    private function sendRefundNotificationToAffiliate(TopUpOrder $topUpOrder, $title)
    {
        try {
            notify()->affiliate($topUpOrder->agent)->send([
                "title" => $title,
                "link" => url("affiliate/" . $topUpOrder->agent->id),
                "type" => 'warning',
                "event_type" => 'App\Models\Affiliate',
                "event_id" => $topUpOrder->agent->id
            ]);
        } catch (\Throwable $e) {
        }
    }
}