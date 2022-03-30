<?php namespace Sheba\Settings\Payment\Responses;


class ValidateResponse
{
    private $agreementId;

    /**
     * @return mixed
     */
    public function getAgreementId()
    {
        return $this->agreementId;
    }

    public function setAgreementId($id)
    {
        $this->agreementId = $id;
        return $this;
    }

}