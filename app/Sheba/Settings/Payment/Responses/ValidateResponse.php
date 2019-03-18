<?php namespace Sheba\Settings\Payment\Responses;


class ValidateResponse
{
    private $agreementId;

    public function __get($name)
    {
        return $this->$name;
    }

    public function setAgreementId($id)
    {
        $this->agreementId = $id;
        return $this;
    }

}