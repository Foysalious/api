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

    public function getCode()
    {
        $this->setCode($this->response['code'] ?? 500);
        return $this->code;
    }

    public function getMessage()
    {
        if (!$this->response) $this->setResponse('Something Went Wrong');
        if ($this->response['code'] == 200) $this->setResponse('Successful');
        $this->setResponse($this->response['msg'] ?? $this->response['message']);
        return $this->message;
    }
}