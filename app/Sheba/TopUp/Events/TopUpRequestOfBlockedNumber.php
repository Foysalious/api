<?php namespace Sheba\TopUp\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Sheba\TopUp\TopUpAgent;

class TopUpRequestOfBlockedNumber extends Event
{
    use SerializesModels;

    public $agent;
    public $blockedMobileNumber;

    /**
     * Create a new event instance.
     *
     * @param TopUpAgent $agent
     * @param $blocked_mobile_number
     */
    public function __construct(TopUpAgent $agent, $blocked_mobile_number)
    {
        $this->agent = $agent;
        $this->blockedMobileNumber = $blocked_mobile_number;
    }
}