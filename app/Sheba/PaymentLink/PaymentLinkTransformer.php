<?php namespace Sheba\PaymentLink;

use Sheba\HasWallet;
use stdClass;

class PaymentLinkTransformer
{
    private $response;

    /**
     * @param stdClass $response
     * @return $this
     */
    public function setResponse(stdClass $response)
    {
        $this->response = $response;
        return $this;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getLinkID()
    {
        return $this->response->linkId;
    }


    public function getReason()
    {
        return $this->response->reason;
    }

    public function getLink()
    {
        return $this->response->link;
    }

    public function getLinkIdentifier()
    {
        return $this->response->linkIdentifier;
    }


    public function getAmount()
    {
        return $this->response->amount;
    }

    public function getIsActive()
    {
        return $this->response->isActive;
    }

    public function getIsDefault()
    {
        return $this->response->isDefault;
    }

    /**
     * @return HasWallet
     */
    public function getPaymentReceiver()
    {
        $model_name = "App\\Models\\" . ucfirst($this->response->userType);
        return $model_name::find($this->response->userId);
    }

    /**
     * @return mixed
     */
    public function getTarget()
    {
        if ($this->response->targetType) {
            $model_name = $this->resolveTargetClass();
            return $model_name::find($this->response->targetId);
        } else
            return null;
    }

    /**
     * @return null
     */
    public function getPayer()
    {
        $order = $this->getTarget();
        return $order ? $order->customer->profile : null;
    }

    private function resolveTargetClass()
    {
        $model_name = "App\\Models\\";
        if ($this->response->targetType == 'pos_order') return $model_name . 'PosOrder';
    }
}