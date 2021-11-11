<?php

namespace Sheba\Payment\Methods\Upay\Response;

class UpayApiResponse
{
    private $server_response;
    private $code;
    private $message;
    const SUCCESS_CODE = 'MS2001';
    private $data;
    private $language;
    private $lang;
    /**
     * @param mixed $server_response
     * @return UpayApiResponse
     */
    public function setServerResponse($server_response)
    {
        $this->server_response = json_decode($server_response, 0);
        foreach ($this->server_response as $key => $value) {
            $this->$key = $value;
        }
        return $this;
    }

    /**
     * @param mixed $code
     * @return UpayApiResponse
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @param mixed $message
     * @return UpayApiResponse
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return json_encode($this->server_response);
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    public function hasError()
    {
        return $this->code !== self::SUCCESS_CODE;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return UpayApiResponse
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }


}