<?php namespace Sheba\Business\BusinessMemberStatusChangeLog;

class Requester
{
    private $fromStatus;
    private $toStatus;
    private $log;

    public function setFromStatus($from_status)
    {
        $this->fromStatus = $from_status;
        return $this;
    }

    public function getFromStatus()
    {
        return $this->fromStatus;
    }

    public function setToStatus($to_status)
    {
        $this->toStatus = $to_status;
        return $this;
    }

    public function getToStatus()
    {
        return $this->toStatus;
    }

    public function getLog()
    {
        return $this->log;
    }
}
