<?php namespace Sheba\Business\CoWorker\Requests;


class Requester
{
    private $status;
    /**
     * @param $status
     * @return Requester
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }
}