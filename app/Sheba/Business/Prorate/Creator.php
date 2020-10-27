<?php namespace Sheba\Business\Prorate;

use Sheba\Dal\BusinessMemberLeaveType\Model as BusinessMemberLeaveType;
use Sheba\Business\Prorate\Requester;

class Creator
{
    /** @var Requester $requester */
    private $requester;


    /**
     * @param Requester $requester
     * @return $this
     */
    public function setRequester(Requester $requester)
    {
        $this->requester = $requester;
        return $this;
    }
}