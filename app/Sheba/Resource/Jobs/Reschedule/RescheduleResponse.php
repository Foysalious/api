<?php namespace Sheba\Resource\Jobs\Reschedule;


use Sheba\Resource\Jobs\Response;

class RescheduleResponse extends Response
{
    protected function setSuccessfulMessage()
    {
        $this->setMessage('আপনার শিডিউল পরিবর্তন টি সফল হয়েছে।');
    }

    protected function setUnsuccessfulMessage()
    {
        $this->setMessage('আপনার শিডিউল পরিবর্তন টি সফল হয় নি। অন্য একটি দিন অথবা সময় নির্ধারিত করে পুনরায় চেষ্টা করুন।');
    }

}