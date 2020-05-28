<?php namespace Sheba\TopUp;

use App\Models\Affiliate;
use App\Models\TopUpOrder;
use Exception;
use App\Models\TopUpVendor;
use Sheba\ModificationFields;
use DB;
use Sheba\TopUp\Vendor\Response\Ipn\SuccessResponse;
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
     * @param TopUpOrder $topup_order
     * @throws Exception
     */
    public function recharge(TopUpOrder $topup_order)
    {
        if ($this->validator->setTopupOrder($topup_order)->validate()->hasError()) {
            $this->updateFailedTopOrder($topup_order, $this->validator->getError());
        } else {
            $this->response = $this->vendor->recharge($topup_order);
            $balance = $this->vendor->getBalance();
            dd($balance->available_credit);
            if ($this->response->hasSuccess()) {
                $response = $this->response->getSuccess();
                DB::transaction(function () use ($response, $topup_order) {
                    $this->setModifier($this->agent);
                    $topup_order = $this->updateSuccessfulTopOrder($topup_order, $response);
                    /** @var TopUpCommission $top_up_commission */
                    $top_up_commission = $this->agent->getCommission();
                    $top_up_commission->setTopUpOrder($topup_order)->disburse();
                    $this->vendor->deductAmount($topup_order->amount);
                    $this->isSuccessful = true;
                });
            } else {
                $this->updateFailedTopOrder($topup_order, $this->response->getError());
            }
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
     * @throws Exception
     */
    public function getError()
    {
        if ($this->validator->hasError()) {
            return $this->validator->getError();
        } else if (!$this->response->hasSuccess()) {
            return $this->response->getError();
        } else {
            if (!$this->isSuccessful) return new TopUpSystemErrorResponse();
        }
        return new TopUpErrorResponse();
    }

    /**
     * @param TopUpOrder $topup_order
     * @param TopUpSuccessResponse $response
     * @return TopUpOrder
     */
    private function updateSuccessfulTopOrder(TopUpOrder $topup_order, TopUpSuccessResponse $response)
    {
        $topup_order->status = $this->vendor->getTopUpInitialStatus();
        $topup_order->transaction_id = $response->transactionId;
        $topup_order->transaction_details = json_encode($response->transactionDetails);
        return $this->updateTopUpOrder($topup_order);

    }

    private function updateFailedTopOrder(TopUpOrder $topup_order, TopUpErrorResponse $response)
    {
        $topup_order->status = config('topup.status.failed')['sheba'];
        $topup_order->transaction_details = json_encode(['code' => $response->errorCode, 'message' => $response->errorMessage, 'response' => $response->errorResponse]);
        return $this->updateTopUpOrder($topup_order);
    }

    private function updateTopUpOrder(TopUpOrder $topup_order)
    {
        $this->withUpdateModificationField($topup_order);
        $topup_order->update();
        $topup_order->agent = $this->agent;
        $topup_order->vendor = $this->model;
        return $topup_order;
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
            $top_up_order->status = config('topup.status.failed')['sheba'];
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
     * @param SuccessResponse $success_response
     * @return bool
     */
    public function processSuccessfulTopUp(TopUpOrder $top_up_order, SuccessResponse $success_response)
    {
        if ($top_up_order->isSuccess()) return true;
        DB::transaction(function () use ($top_up_order, $success_response) {
            $top_up_order->status = config('topup.status.successful')['sheba'];
            $top_up_order->transaction_details = json_encode($success_response->getSuccessfulTransactionDetails());
            $top_up_order->update();
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
