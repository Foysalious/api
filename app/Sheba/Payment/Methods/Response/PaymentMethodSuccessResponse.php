<?php namespace Sheba\Payment\Methods\Response;


class PaymentMethodSuccessResponse
{
    private $id;
    private $details;
    private $redirect_url;
    private $refund_id;

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}
