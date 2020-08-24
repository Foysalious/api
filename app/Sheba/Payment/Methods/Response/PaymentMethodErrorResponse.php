<?php namespace Sheba\Payment\Methods\Response;

class PaymentMethodErrorResponse
{
    private $id;
    private $code;
    private $message;
    private $details;

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    /**
     * @return array
     */
    public function getGatewayResponse()
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'message' => $this->message,
            'details' => $this->details
        ];
    }
}