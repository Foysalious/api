<?php

namespace Sheba\TopUp\Vendor\Response;


class TopUpErrorResponse
{
    protected $errorCode;
    protected $errorMessage;
    protected $errorResponse;

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function toArray()
    {
        return [
            'code' => $this->errorCode,
            'message' => $this->errorMessage,
            'response' => $this->errorResponse
        ];
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }
}
