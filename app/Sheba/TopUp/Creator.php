<?php


namespace Sheba\TopUp;


use App\Models\TopUpOrder;
use Sheba\ModificationFields;

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
        $vendor = $this->topUpRequest->getVendor();
        $model = $vendor->getModel();
        $top_up_order->agent_type = "App\\Models\\" . class_basename($this->topUpRequest->getAgent());
        $top_up_order->agent_id = $agent->id;
        $top_up_order->payee_mobile = $this->topUpRequest->getMobile();
        $top_up_order->amount = $this->topUpRequest->getAmount();
        $top_up_order->status = "Pending";
        $top_up_order->vendor_id = $model->id;
        $top_up_order->sheba_commission = ($this->topUpRequest->getAmount() * $model->sheba_commission) / 100;
        $this->setModifier($agent);
        $this->withCreateModificationField($top_up_order);
        $top_up_order->save();
        return $top_up_order;
    }
}