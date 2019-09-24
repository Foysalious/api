<?php namespace App\Jobs;

use App\Models\Partner;
use App\Models\PartnerSubscriptionPackage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Sheba\SmsHandler;

class PartnerRenewalSMS extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /** @var Partner $partner */
    private $partner;
    /** @var PartnerSubscriptionPackage $package */
    private $package;
    private $subscription_amount;

    /**
     * Create a new job instance.
     *
     * @param Partner $partner
     */
    public function __construct(Partner $partner)
    {
        $this->partner = $partner;
    }

    /**
     * @param PartnerSubscriptionPackage $package
     * @return $this
     */
    public function setPackage(PartnerSubscriptionPackage $package)
    {
        $this->package = $package;
        return $this;
    }

    public function setSubscriptionAmount($subscription_amount)
    {
        $this->subscription_amount = $subscription_amount;
        return $this;
    }

    public function handle()
    {
        (new SmsHandler('renew-subscription'))->send($this->partner->getContactNumber(), [
            'package_name' => $this->package->show_name_bn,
            'package_type' => $this->partner->billing_type,
            'formatted_package_type' => $this->partner->billing_type == 'monthly' ? 'মাসের' : 'বছরের',
            'subscription_amount' => $this->subscription_amount
        ]);
    }
}
