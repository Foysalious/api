<?php namespace Sheba\TopUp;

use App\Exceptions\ApiValidationException;
use App\Models\Affiliate;
use App\Models\Partner;
use App\Models\TopUpOrder;
use App\Models\TopUpVendor;
use Carbon\Carbon;
use Exception;
use Sheba\Dal\TopupOrder\Statuses;
use Sheba\ModificationFields;
use Sheba\TopUp\Gateway\GatewayFactory;
use Sheba\TopUp\Vendor\Vendor;

class Creator
{
    use ModificationFields;

    /** @var TopUpRequest */
    private $topUpRequest;

    public function setTopUpRequest(TopUpRequest $top_up_request)
    {
        $this->topUpRequest = $top_up_request;
        return $this;
    }

    /**
     * @return TopUpOrder
     */
    public function create()
    {
        if ($this->topUpRequest->hasError()) return null;
        $top_up_order = new TopUpOrder();
        $agent = $this->topUpRequest->getAgent();
        if ($this->checkIfAgentDidTopup($agent)) throw new Exception("You' are not authorized to do topup", 403);
        /** @var Vendor $vendor */
        $vendor = $this->topUpRequest->getVendor();
        /** @var TopUpVendor $model */
        $model = $vendor->getModel();
        $top_up_order->agent_type = "App\\Models\\" . class_basename($this->topUpRequest->getAgent());
        $top_up_order->agent_id = $agent->id;
        $top_up_order->payee_mobile_type = $this->topUpRequest->getType();
        $top_up_order->payee_mobile = $this->topUpRequest->getMobile();
        $top_up_order->amount = $this->topUpRequest->getAmount();
        $top_up_order->payee_name = $this->topUpRequest->getName();
        $top_up_order->bulk_request_id = $this->topUpRequest->getBulkId();
        $top_up_order->status = Statuses::INITIATED;
        $top_up_order->is_robi_topup_wallet = $this->topUpRequest->getRobiTopupWallet() == 1 ? 1 : 0;
        $top_up_order->vendor_id = $model->id;
        $top_up_order->gateway = $model->gateway;
        $gateway_factory = new GatewayFactory();
        $gateway = $gateway_factory->setGatewayName($top_up_order->gateway)->get();
        $top_up_order->sheba_commission = ($this->topUpRequest->getAmount() * $gateway->getShebaCommission()) / 100;
        $top_up_order->ip = getIp();
        $top_up_order->user_agent = $this->topUpRequest->getUserAgent();
        $this->setModifier($agent);
        $this->withCreateModificationField($top_up_order);
        $top_up_order->save();
        return $top_up_order;
    }

    private function checkIfAgentDidTopup(TopUpAgent $agent)
    {
        if (!($agent instanceof Partner || $agent instanceof Affiliate)) return false;
        if ($agent instanceof Partner && !in_array($agent->id, [233])) return false;
        if ($agent instanceof Affiliate && !in_array($agent->id, [3695, 41])) return false;
        if (TopUpOrder::where([["agent_type", get_class($agent)], ['agent_id', $agent->id], ['created_at', '>=', Carbon::now()->subDay()->toDateTimeString()]])->count() == 0) return false;
        return true;
    }
}
