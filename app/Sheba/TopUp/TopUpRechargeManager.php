<?php namespace Sheba\TopUp;

use App\Models\TopUpOrder;
use Exception;
use App\Models\TopUpVendor;
use Sheba\ModificationFields;
use DB;
use Sheba\Reward\ActionRewardDispatcher;
use Sheba\TopUp\Vendor\Response\TopUpErrorResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;
use Sheba\TopUp\Vendor\Response\TopUpSuccessResponse;
use Sheba\TopUp\Vendor\Response\TopUpSystemErrorResponse;
use Sheba\TopUp\Vendor\Vendor;

class TopUpRechargeManager extends TopUpManager
{
    use ModificationFields;

    /** @var TopUpValidator */
    private $validator;

    /** @var Vendor */
    private $vendor;
    /** @var TopUpVendor */
    private $vendorModel;
    /** @var TopUpAgent */
    private $agent;
    /** @var boolean */
    private $isSuccessful;
    /** @var TopUpResponse */
    private $response;

    public function __construct(TopUpValidator $validator, StatusChanger $status_changer)
    {
        parent::__construct($status_changer);
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
     * @param Vendor $vendor
     * @return $this
     */
    public function setVendor(Vendor $vendor)
    {
        $this->vendor = $vendor;
        $this->vendorModel = $this->vendor->getModel();
        $this->validator->setVendor($vendor);
        return $this;
    }

    /**
     * @param TopUpOrder $order
     * @return $this
     */
    public function setTopUpOrder(TopUpOrder $order)
    {
        parent::setTopUpOrder($order);
        $this->validator->setTopupOrder($this->topUpOrder);
        return $this;
    }

    /**
     * @throws Exception
     */
    public function recharge()
    {
        if ($this->validator->validate()->hasError()) {
            $this->updateFailedTopOrder($this->validator->getError());
            return;
        }

        $this->response = $this->vendor->recharge($this->topUpOrder);

        if ($this->response->hasError()) {
            $this->updateFailedTopOrder($this->response->getErrorResponse());
            return;
        }

        $response = $this->response->getSuccess();

        try {
            DB::transaction(function () use ($response) {
                $this->topUpOrder = $this->updateSuccessfulTopOrder($response);
                $this->agent->getCommission()->setTopUpOrder($this->topUpOrder)->disburse();
                $this->vendor->deductAmount($this->topUpOrder->amount);
            });
        } catch (Exception $e) {
            $this->markOrderAsSystemError($e);
        }

        if ($this->topUpOrder->isAgentPartner()) {
            app()->make(ActionRewardDispatcher::class)->run('top_up', $this->agent, $this->topUpOrder);
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
     * @param TopUpSuccessResponse $response
     * @return TopUpOrder
     */
    private function updateSuccessfulTopOrder(TopUpSuccessResponse $response)
    {
        $id = $response->getTransactionId();
        $details = $response->getTransactionDetailsAsString();

        $topup_order = $response->isPending() ?
            $this->statusChanger->pending($id, $details) :
            $this->statusChanger->successful($id, $details);

        return $this->setAgentAndVendor($topup_order);
    }

    /**
     * @param TopUpErrorResponse $response
     * @return TopUpOrder
     */
    private function updateFailedTopOrder(TopUpErrorResponse $response)
    {
        $topup_order = $this->statusChanger->failed($response->getFailedReason(), $response->toJson());
        return $this->setAgentAndVendor($topup_order);
    }

    /**
     * @param TopUpOrder $topup_order
     * @return TopUpOrder
     */
    private function setAgentAndVendor(TopUpOrder $topup_order)
    {
        $topup_order->agent = $this->agent;
        $topup_order->vendor = $this->vendorModel;
        return $topup_order;
    }
}
