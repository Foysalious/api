<?php namespace Sheba\TopUp;

use App\Models\TopUpOrder;
use Exception;
use App\Models\TopUpVendor;
use Sheba\Dal\TopupOrder\FailedReason;
use Sheba\Dal\TopupOrder\Statuses;
use Sheba\ModificationFields;
use DB;
use Sheba\Reward\ActionRewardDispatcher;
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
    /** @var boolean */
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
        $this->setModifier($this->agent);
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
            return;
        }

        $this->response = $this->vendor->recharge($topup_order);

        if ($this->response->hasError()) {
            $this->updateFailedTopOrder($topup_order, $this->response->getErrorResponse());
            return;
        }

        $response = $this->response->getSuccess();

        try {
            DB::transaction(function () use ($response, &$topup_order) {
                $topup_order = $this->updateSuccessfulTopOrder($topup_order, $response);
                $this->agent->getCommission()->setTopUpOrder($topup_order)->disburse();
                $this->vendor->deductAmount($topup_order->amount);
            });
        } catch (Exception $e) {
            $this->markOrderAsSystemError($topup_order, $e);
        }

        if ($topup_order->isAgentPartner()) {
            app()->make(ActionRewardDispatcher::class)->run('top_up', $this->agent, $topup_order);
        }
        $this->isSuccessful = true;
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
        if ($this->validator->hasError()) return $this->validator->getError();
        if ($this->response->hasError()) return $this->response->getErrorResponse();
        if (!$this->isSuccessful) return new TopUpSystemErrorResponse();
        return new TopUpErrorResponse();
    }

    /**
     * @param TopUpOrder           $topup_order
     * @param TopUpSuccessResponse $response
     * @return TopUpOrder
     */
    private function updateSuccessfulTopOrder(TopUpOrder $topup_order, TopUpSuccessResponse $response)
    {
        $topup_order->status = $response->getTopUpStatus();
        $topup_order->transaction_id = $response->getTransactionId();
        $topup_order->transaction_details = $response->getTransactionDetailsAsString();
        return $this->updateTopUpOrder($topup_order);
    }

    private function updateFailedTopOrder(TopUpOrder $topup_order, TopUpErrorResponse $response)
    {
        $topup_order->status = Statuses::FAILED;
        $topup_order->failed_reason = $response->getFailedReason();
        $topup_order->transaction_details = $response->toJson();
        return $this->updateTopUpOrder($topup_order);
    }

    /**
     * @param TopUpOrder $topup_order
     * @return TopUpOrder
     */
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
     * @throws Exception
     */
    public function processFailedTopUp(TopUpOrder $top_up_order, TopUpFailResponse $top_up_fail_response)
    {
        if ($top_up_order->isFailed()) return;

        try {
            DB::transaction(function () use ($top_up_order, $top_up_fail_response) {
                $this->model = $top_up_order->vendor;
                $top_up_order->status = Statuses::FAILED;
                $top_up_order->failed_reason = FailedReason::GATEWAY_ERROR;
                $top_up_order->transaction_details = json_encode($top_up_fail_response->getFailedTransactionDetails());
                $this->setModifier($this->agent);
                $this->withUpdateModificationField($top_up_order);
                $top_up_order->update();
                $this->refund($top_up_order);
                $vendor = new VendorFactory();
                $vendor = $vendor->getById($top_up_order->vendor_id);
                $vendor->refill($top_up_order->amount);
            });
        } catch (Exception $e) {
            $this->markOrderAsSystemError($top_up_order, $e);
            throw $e;
        }
    }

    /**
     * @param TopUpOrder $top_up_order
     * @param SuccessResponse $success_response
     * @throws Exception
     */
    public function processSuccessfulTopUp(TopUpOrder $top_up_order, SuccessResponse $success_response)
    {
        if ($top_up_order->isSuccess()) return;

        try {
            DB::transaction(function () use ($top_up_order, $success_response) {
                $top_up_order->status = Statuses::SUCCESSFUL;
                $top_up_order->transaction_details = json_encode($success_response->getSuccessfulTransactionDetails());
                $top_up_order->update();
            });
        } catch (Exception $e) {
            $this->markOrderAsSystemError($top_up_order, $e);
            throw $e;
        }
    }

    private function markOrderAsSystemError(TopUpOrder $top_up_order, Exception $e)
    {
        logErrorWithExtra($e, ['topup' => $top_up_order->getDirty()]);
        $top_up_order->update(['status' => Statuses::SYSTEM_ERROR]);
    }

    /**
     * @param TopUpOrder $top_up_order
     */
    public function refund(TopUpOrder $top_up_order)
    {
        $top_up_order->agent->getCommission()->setTopUpOrder($top_up_order)->refund();
    }
}
