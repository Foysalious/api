<?php namespace Sheba\TopUp\FailedReason;

class PretupsFailedReason extends FailedReason
{
    public function getReason()
    {
        return json_decode($this->transaction)->message;
    }
}