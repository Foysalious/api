<?php namespace Sheba\Customer\Jobs\Reschedule;

use Sheba\Resource\Jobs\Response;

class RescheduleResponse extends Response
{
    public function setSuccessfulMessage()
    {
        $this->setMessage('Reschedule Successful!');
    }

    public function setUnsuccessfulMessage()
    {
        $this->setMessage('Reschedule Failed.');
    }
}