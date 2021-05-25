<?php namespace Sheba\Business\LeaveRejection;


class Requester
{
    private $reasons;
    private $note;

    public function setReasons($reasons)
    {
        $this->reasons = $reasons;
        if ($this->reasons) $this->reasons = json_decode($this->reasons, 1);
        return $this;
    }

    public function getReasons()
    {
        return $this->reasons;
    }

    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    public function getNote()
    {
        return $this->note;
    }

}