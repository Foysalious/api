<?php namespace Sheba\Resource\Jobs;


class Response
{
    protected $response;

    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    public function getCode()
    {
        return $this->response['code'] ?? 500;
    }

    public function getMessage()
    {
        if (!$this->response) return 'Something Went Wrong';
        if ($this->response['code'] == 200) return 'Successful';
        return $this->response['msg'] ?? $this->response['message'];
    }
}