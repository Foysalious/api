<?php namespace Sheba\Resource\Jobs\Updater;


class StatusUpdateResponse
{
    private $response;

    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    public function getCode()
    {
        return $this->response ? $this->response['code'] : 500;
    }

    public function getMessage()
    {
        if ($this->response) return 'Something Went Wrong';
        if ($this->response['code'] == 200) return 'Successful';
        return $this->response['msg'];
    }
}