<?php namespace Sheba\PaymentLink;


class PaymentLinkTransformer
{
    private $response;

    public function setResponse(\stdClass $response)
    {
        $this->response = $response;
        return $this;
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

    public function getUser()
    {
        $model_name = ucfirst($this->response->userType);
        return $model_name::find($this->response->userId);
    }

    /**
     * @return mixed
     */
    public function getTarget()
    {
        if ($this->response->target) {
            $model_name = ucfirst($this->response->targetType);
            return $model_name::find($this->response->targetId);
        } else
            return null;
    }
}