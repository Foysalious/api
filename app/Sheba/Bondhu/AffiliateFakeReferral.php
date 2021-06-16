<?php namespace Sheba\Bondhu;

use App\Sheba\Sms\BusinessType;
use App\Sheba\Sms\FeatureType;
use Sheba\Sms\Sms;
use App\Models\Affiliate;
use Carbon\Carbon;
use Sheba\Notification\DataHandler;

class AffiliateFakeReferral
{
    private $affiliate;

    private $suspensionLimit;
    private $warnLimit;

    private $notificationData;
    private $sms;

    public function __construct(DataHandler $notification_data, Sms $sms)
    {
        $this->notificationData = $notification_data;
        $this->suspensionLimit = constants('AFFILIATE_SUSPENSION_FOR_NO_OF_FAKE');
        $this->warnLimit = constants('AFFILIATE_WARN_FOR_NO_OF_FAKE');
        $this->sms = $sms;
    }

    public function setAffiliate(Affiliate $affiliate)
    {
        $this->affiliate = $affiliate;
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function takeActions()
    {
        if($this->affiliate->fake_referral_counter == $this->suspensionLimit - 1) {
            $this->suspend();
        } else if($this->affiliate->fake_referral_counter == $this->warnLimit - 1) {
            $this->warn();
        }
        $this->affiliate->increment('fake_referral_counter');
    }

    /**
     * @throws \Exception
     */
    private function suspend()
    {
        $this->affiliate->update([
            "is_suspended" => 1,
            "last_suspended_at" => Carbon::now()
        ]);

        notify()->affiliate($this->affiliate)->send($this->notificationData->affiliateSuspend($this->affiliate));
    }

    private function warn()
    {
        $sms = "You are referencing too many false numbers. Stop doing that, or your account will be suspended.";
        $this->sms
            ->setFeatureType(FeatureType::AFFILIATE_FAKE_REFERRAL)
            ->setBusinessType(BusinessType::BONDHU)
            ->shoot($this->affiliate->profile->mobile, $sms);
    }
}