<?php namespace Sheba\TopUp\Listeners;

use App\Models\Affiliate;
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
        $event->topupRequest->getAgent()->update(['verification_status' => 'rejected']);
    }

    private function notifyConcerningPersons(TopUpRequestOfBlockedNumberEvent $event)
    {
        $receivers = TopUpTransactionBlockNotificationReceiver::with('user')->get();
        foreach ($receivers as $receiver) {
            try {
                $this->sms->shoot(BDMobileFormatter::format($receiver->user->mobile), "Topup request blocked for trying to recharge to this blocked number, ");
            } catch (RequestException $exception) {
            }
        }
    }
}