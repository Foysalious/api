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
        if (!$event->agent instanceof Affiliate) return;
        $event->agent->update(['verification_status' => 'rejected']);
        $receivers = TopUpTransactionBlockNotificationReceiver::with('user')->get();
        foreach ($receivers as $receiver) {
            try {
                $this->sms->shoot(BDMobileFormatter::format($receiver->user->mobile), "Someone tried to recharge to this blocked number, " . $event->blockedMobileNumber);
            } catch (RequestException $exception) {
            }
        }
    }
}