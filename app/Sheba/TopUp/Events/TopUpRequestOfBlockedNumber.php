<?php namespace Sheba\TopUp\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Sheba\TopUp\TopUpRequest;

class TopUpRequestOfBlockedNumber extends Event
{
    use SerializesModels;

    /**
     * @var TopUpRequest
     */
    public $topupRequest;


    /**
     * TopUpRequestOfBlockedNumber constructor.
     * @param TopUpRequest $request
     */
    public function __construct(TopUpRequest $request)
    {
        $this->topupRequest = $request;
    }
}
