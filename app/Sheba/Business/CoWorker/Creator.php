<?php namespace Sheba\Business\CoWorker;

use Sheba\Business\CoWorker\Requests\BasicRequest;

class Creator
{
    /** @var BasicRequest $basicRequest */
    private $basicRequest;
    
    public function setBasicRequest(BasicRequest $basic_request)
    {
        $this->basicRequest = $basic_request;
        return $this;
    }

    public function storeBasicInfo()
    {
        return;
    }
}