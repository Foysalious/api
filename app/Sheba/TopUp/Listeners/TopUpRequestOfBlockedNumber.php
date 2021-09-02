<?php namespace Sheba\TopUp\Listeners;

use App\Models\Affiliate;
use App\Models\CanTopUpUpdateLog;
use App\Models\Partner;

use App\Repositories\SmsHandler;
use App\Sheba\Sms\BusinessType;
use App\Sheba\Sms\FeatureType;
use GuzzleHttp\Exception\RequestException;
use Sheba\Dal\TopUpTransactionBlockNotificationReceiver\TopUpTransactionBlockNotificationReceiver;
use Sheba\Helpers\Formatters\BDMobileFormatter;
use Sheba\Sms\Sms;
use Sheba\TopUp\Events\TopUpRequestOfBlockedNumber as TopUpRequestOfBlockedNumberEvent;
use Carbon\Carbon;


class TopUpRequestOfBlockedNumber
{
    private $sms;

    private $topUpRequest;

    public function __construct(Sms $sms)
    {
        $this->sms = $sms;

    }


    public function handle(TopUpRequestOfBlockedNumberEvent $event)
    {
        $this->blockUser($event);
        $this->notifyConcerningPersons($event);
    }

    private function blockUser(TopUpRequestOfBlockedNumberEvent $event )
    {
        if ($event->topupRequest->getAgent() instanceof Affiliate) $event->topupRequest->getAgent()->update(['verification_status' => 'rejected', 'reject_reason' => "Unusual / Suspicious account activity"]);
        elseif ($event->topupRequest->getAgent() instanceof Partner) {
            $event->topupRequest->getAgent()->update(['can_topup' => 0] );
            $event->topupRequest->getAgent()->topupChangeLogs()->create(['from' => 1, 'to' => 0, 'created_at'=>Carbon::now(), 'partner_id' => $event->topupRequest->getAgent()->id,  'log' => 'Partner has been blacklisted due to top up request to this'. $event->topupRequest->getMobile()]);
        }
    }

    private function notifyConcerningPersons(TopUpRequestOfBlockedNumberEvent $event)
    {
        $receivers = TopUpTransactionBlockNotificationReceiver::with('user')->get();
        foreach ($receivers as $receiver) {
            try {
                (new SmsHandler('affiliate-rejected-for-block-topup'))
                    ->setBusinessType(BusinessType::BONDHU)
                    ->setFeatureType(FeatureType::TOP_UP)
                    ->send(BDMobileFormatter::format($receiver->user->mobile), [
                        'blocked_number' => $event->topupRequest->getMobile(),
                        'amount' => $event->topupRequest->getAmount(),
                        'agent_id' => $event->topupRequest->getAgent()->id,
                        'agent_number' => $event->topupRequest->getAgent()->getMobile(),
                    ]);
            } catch (RequestException $exception) {
            }
        }
    }


}
