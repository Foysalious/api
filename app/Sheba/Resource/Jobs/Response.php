<?php namespace Sheba\Resource\Jobs;


class Response
{
    protected $response;
    protected $code;
    protected $message;

    /**
     * @param mixed $code
     * @return Response
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @param mixed $message
     * @return Response
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getCode()
    {
        if ($this->code) return $this->code;
        $this->setCode($this->response['code'] ?? 500);
        return $this->code;
    }

    public function getMessage()
    {
        if ($this->message) return $this->message;
        if (!$this->response) $this->setUnsuccessfulMessage();
        if ($this->response['code'] == 200) $this->setSuccessfulMessage();
        else $this->setUnsuccessfulMessage();
        return $this->message;
    }

    public function setSuccessfulMessage()
    {
        $this->setMessage('Successful');
        return $this;
    }

    public function setSuccessfulCode()
    {
        $this->setCode(200);
        return $this;
    }

    public function setUnsuccessfulMessage()
    {
        $this->setMessage('Something Went Wrong');
        return $this;
    }

}