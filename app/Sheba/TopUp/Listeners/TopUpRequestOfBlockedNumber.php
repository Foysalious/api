<?php namespace Sheba\TopUp\Listeners;

use App\Models\Affiliate;
use App\Repositories\SmsHandler;
use GuzzleHttp\Exception\RequestException;
use Sheba\Dal\TopUpTransactionBlockNotificationReceiver\TopUpTransactionBlockNotificationReceiver;
use Sheba\Helpers\Formatters\BDMobileFormatter;
use Sheba\Sms\Sms;
use Sheba\TopUp\Events\TopUpRequestOfBlockedNumber as TopUpRequestOfBlockedNumberEvent;

class TopUpRequestOfBlockedNumber
{
    private $sms;

    public function __construct(Sms $sms)
    {
        $this->sms = $sms;
    }


    public function handle(TopUpRequestOfBlockedNumberEvent $event)
    {
        $this->blockUser($event);
        $this->notifyConcerningPersons($event);
    }

    private function blockUser(TopUpRequestOfBlockedNumberEvent $event)
    {
        if (!$event->topupRequest->getAgent() instanceof Affiliate) return;
        $event->topupRequest->getAgent()->update(['verification_status' => 'rejected', 'reject_reason' => "Unusual / Suspicious account activity"]);
    }

    private function notifyConcerningPersons(TopUpRequestOfBlockedNumberEvent $event)
    {
        $receivers = TopUpTransactionBlockNotificationReceiver::with('user')->get();
        foreach ($receivers as $receiver) {
            try {
                (new SmsHandler('affiliate-rejected-for-block-topup'))->send(BDMobileFormatter::format($receiver->user->mobile), [
                    'blocked_number' => $event->topupRequest->getMobile(),
                    'amount' => $event->topupRequest->getAmount(),
                    'agent_id' => $event->topupRequest->getAgent()->id,
                    'agent_number' => $event->topupRequest->getAgent()->profile->mobile,
                ]);
            } catch (RequestException $exception) {
            }
        }
    }
}