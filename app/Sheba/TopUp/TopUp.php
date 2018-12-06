<?php namespace Sheba\TopUp;

use App\Models\Affiliate;
use App\Models\TopUpOrder;
use Sheba\TopUp\Commission\CommissionFactory;
use App\Models\TopUpVendor;
use Sheba\ModificationFields;
use DB;
use Sheba\TopUp\Vendor\Response\TopUpErrorResponse;
use Sheba\TopUp\Vendor\Response\TopUpFailResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;
use Sheba\TopUp\Vendor\Response\TopUpSuccessResponse;
use Sheba\TopUp\Vendor\Response\TopUpSystemErrorResponse;
use Sheba\TopUp\Vendor\Vendor;
use Sheba\TopUp\Vendor\VendorFactory;

class TopUp
{
    use ModificationFields;
    /** @var Vendor */
    private $vendor;
    /** @var TopUpVendor */
    private $model;
    /** @var TopUpAgent */
    private $agent;

    private $isSuccessful;

    /** @var TopUpResponse */
    private $response;

    /** @var TopUpValidator */
    private $validator;

    public function __construct(TopUpValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param TopUpAgent $agent
     * @return $this
     */
    public function setAgent(TopUpAgent $agent)
    {
        $this->agent = $agent;
        $this->validator->setAgent($agent);
        return $this;
    }

    /**
     * @param Vendor $model
     * @return $this
     */
    public function setVendor(Vendor $model)
    {
        $this->vendor = $model;
        $this->model = $this->vendor->getModel();
        $this->validator->setVendor($model);
        return $this;
    }

    /**
     * @param TopUpRequest $top_up_request
     */
    public function recharge(TopUpRequest $top_up_request)
    {
        if($this->validator->setRequest($top_up_request)->validate()->hasError()) return;

        $this->response = $this->vendor->recharge($top_up_request);
        if ($this->response->hasSuccess()) {
            $response = $this->response->getSuccess();
            DB::transaction(function () use ($response, $top_up_request) {
                $this->placeTopUpOrder($response, $top_up_request->getMobile(), $top_up_request->getAmount());
                $amount_after_commission = $top_up_request->getAmount() - $this->agent->calculateCommission($top_up_request->getAmount(), $this->model);
                $this->agent->topUpTransaction($amount_after_commission, $top_up_request->getAmount() . " has been topped up to " . $top_up_request->getMobile());
                $this->vendor->deductAmount($top_up_request->getAmount());
                $this->isSuccessful = true;
            });
        }
    }

    /**
     * @return bool
     */
    public function isNotSuccessful()
    {
        return !$this->isSuccessful;
    }

    /**
     * @return TopUpErrorResponse
     */
    public function getError()
    {
        if($this->validator->hasError()) {
            return $this->validator->getError();
        } else if(!$this->response->hasSuccess()) {
            return $this->response->getError();
        } else {
            if(!$this->isSuccessful) return new TopUpSystemErrorResponse();
        }
        return new TopUpErrorResponse();
    }

    /**
     * @param TopUpSuccessResponse $response
     * @param $mobile_number
     * @param $amount
     */
    private function placeTopUpOrder(TopUpSuccessResponse $response, $mobile_number, $amount)
    {
        $top_up_order = new TopUpOrder();
        $top_up_order->agent_type = "App\\Models\\" . class_basename($this->agent);
        $top_up_order->agent_id = $this->agent->id;
        $top_up_order->payee_mobile = $mobile_number;
        $top_up_order->amount = $amount;
        $top_up_order->status = 'Successful';
        $top_up_order->transaction_id = $response->transactionId;
        $top_up_order->transaction_details = json_encode($response->transactionDetails);
        $top_up_order->vendor_id = $this->model->id;
        $top_up_order->sheba_commission = ($amount * $this->model->sheba_commission) / 100;

        $this->setModifier($this->agent);
        $this->withCreateModificationField($top_up_order);
        $top_up_order->save();

        $top_up_order->agent = $this->agent;
        $top_up_order->vendor = $this->model;

        $this->agent->getCommission()->setTopUpOrder($top_up_order)->disburse();
    }

    /**
     * @param TopUpOrder $top_up_order
     * @param TopUpFailResponse $top_up_fail_response
     * @return bool
     */
    public function processFailedTopUp(TopUpOrder $top_up_order, TopUpFailResponse $top_up_fail_response)
    {
        if ($top_up_order->isFailed()) return true;
        DB::transaction(function () use ($top_up_order, $top_up_fail_response) {
            $this->model = $top_up_order->vendor;
            $top_up_order->status = 'Failed';
            $top_up_order->transaction_details = json_encode($top_up_fail_response->getFailedTransactionDetails());
            $this->setModifier($this->agent);
            $this->withUpdateModificationField($top_up_order);
            $top_up_order->update();
            $this->refund($top_up_order);
            $vendor = new VendorFactory();
            $vendor = $vendor->getById($top_up_order->vendor_id);
            $vendor->refill($top_up_order->amount);
        });
    }

    /**
     * @param TopUpOrder $top_up_order
     */
    public function refund(TopUpOrder $top_up_order)
    {
        $top_up_order->agent->getCommission()->setTopUpOrder($top_up_order)->refund();
    }
}