<?php namespace Sheba\TopUp;

use App\Models\TopUpOrder;
use App\Models\TopUpVendor;
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
        $top_up_order->status = config('topup.status.initiated')['sheba'];
        $top_up_order->vendor_id = $model->id;
        $top_up_order->gateway = $model->gateway;
        $gateway_factory = new GatewayFactory();
        $gateway_factory->setGatewayName($top_up_order->gateway)->setVendorId($top_up_order->vendor_id);
        $gateway = $gateway_factory->get();
        $top_up_order->sheba_commission = ($this->topUpRequest->getAmount() * $gateway->getShebaCommission()) / 100;
        $this->setModifier($agent);
        $this->withCreateModificationField($top_up_order);
        $top_up_order->save();
        return $top_up_order;
    }
}
