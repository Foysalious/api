<?php namespace Sheba\Subscription\Partner;

use App\Models\Partner;
use App\Models\PartnerSubscriptionPackage;
use App\Models\PartnerSubscriptionUpdateRequest;
use App\Sheba\Partner\PackageFeatureCount;
use Sheba\ModificationFields;
use Sheba\Subscription\Exceptions\InvalidPreviousSubscriptionRules;

class PartnerSubscription
{
    use ModificationFields;

    private $requested_package, $upgrade_request;

    private $notification = 0;

    /**
     * @var Partner
     */
    private $partner;

    public function setRequestedPackage($id = null)
    {
        $package_id = $id ? $id : config('sheba.partner_registration_package_id');
        $this->requested_package = PartnerSubscriptionPackage::find($package_id);
        return $this;
    }

    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function setNotification($notification)
    {
        $this->notification = $notification;
        return $this;
    }

    /**
     * @param $resource
     * @return PartnerSubscription
     */
    public function createBasicSubscriptionRequest($resource)
    {
        $running_discount = $this->requested_package->runningDiscount(BillingType::MONTHLY);
        $this->setModifier($resource);
        $update_request_data = $this->withCreateModificationField([
            'partner_id' => $this->partner->id, 'old_package_id' => $this->partner->package_id ?: 1, 'new_package_id' => config('sheba.partner_basic_packages_id'), 'old_billing_type' => $this->partner->billing_type ?: BillingType::MONTHLY, 'new_billing_type' => BillingType::MONTHLY, 'discount_id' => $running_discount ? $running_discount->id : null,'status'=>'Approved'
        ]);
        $this->upgrade_request = PartnerSubscriptionUpdateRequest::create($update_request_data);
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function updateSubscription()
    {
        if($hasCredit = $this->partner->hasCreditForSubscription($this->requested_package, BillingType::MONTHLY) && $this->upgrade_request)
            $this->partner->subscriptionUpgrade($this->requested_package, $this->upgrade_request, $this->notification);
    }

    /**
     * @param Partner $partner
     * @param $partner_subscription_package
     * @return array
     */
    public function formatCurrentPackageData(Partner $partner, PartnerSubscriptionPackage $partner_subscription_package)
    {
        $features = [];
        $price_bn = convertNumbersToBangla($partner->subscription->originalPrice($partner->billing_type));
        $billing_type_bn = $partner->subscription->titleTypeBn($partner->billing_type);
        $features_count = $this->packageFeatureCount($partner->id);
        if ($features_count) {
            $features_count_list = $this->formatFeatureCountList($features_count);
            foreach ($features_count as $key => $value)
            {
                if ($value != 'unlimited' && $value == 0) {
                    array_push($features, $key);
                }
            }
            $features_message = (new SubscriptionFeatureMessage())->getMessage($features);
        }
        // two api for current subscription. DashboardController@getCurrentPackage is another one
        return [
            'current_package'            => $partner_subscription_package,
            'package_feature_count_list' => $features_count_list ?? null,
            'feature_message'            => $features_message ?? null,
            'billing_type'               => $partner->billing_type,
            'last_billing_date'          => $partner->last_billed_date ? $partner->last_billed_date->format('Y-m-d') : null,
            'next_billing_date'          => $partner->periodicBillingHandler()->nextBillingDate() ? $partner->periodicBillingHandler()->nextBillingDate()->format('Y-m-d'): null,
            'validity_remaining_in_days' => $partner->last_billed_date ? $partner->periodicBillingHandler()->remainingDay() : null,
            'is_auto_billing_activated'  => (bool)($partner->auto_billing_activated),
            'static_message'             => $partner_subscription_package->id === (int)SubscriptionStatics::getLitePackageID() ? SubscriptionStatics::getLitePackageMessage() : '',
            'dynamic_message'            => SubscriptionStatics::getPackageMessage($partner, $price_bn),
            'price_bn'                   => $price_bn,
            'billing_type_bn'            => $billing_type_bn,
            'subscription_renewal_warning' => (bool)($partner->subscription_renewal_warning),
            'renewal_warning_days'       => $partner->renewal_warning_days,
        ];
    }

    /**
     * @param $package
     * @param Partner|null $partner
     * @param false $single
     */
    public function dataFormat($package, Partner $partner = null, $single = false)
    {
        $featured_package_id     = config('partner.subscription_featured_package_id');
        if (!$single) $package['rules']        = (json_decode($package->rules, 1));
        $package['is_published'] = $package->name == 'LITE' ? 0 : 1;
        if (!$single) $package['usps']         = $package->usps ? json_decode($package->usps) : ['usp' => [], 'usp_bn' => []];
        $package['features']     = $package->features ? json_decode($package->features) : [];
        $package['is_featured']  = in_array($package->id, $featured_package_id);
        $package['web_view']     = config('sheba.partners_base_url')."/api/packages/".$package->id;

        if ($partner) {
            $package['is_subscribed']     = (int)($partner->package_id == $package->id);
            $package['subscription_type'] = ($partner->package_id == $package->id) ? $partner->billing_type : null;
        }
        removeRelationsAndFields($package);
    }

    /**
     * @param Partner $partner
     * @param $partner_subscription_packages
     * @return array
     * @throws InvalidPreviousSubscriptionRules
     */
    public function allPackagesData(Partner $partner, $partner_subscription_packages)
    {
        $partner_subscription_package  = $partner->subscription;
        list($remaining, $wallet, $bonus_wallet, $threshold) = $partner->getCreditBreakdown();
        $data = [
            'subscription_package'       => $partner_subscription_packages,
            'billing_type'               => $partner->billing_type,
            'current_package'            => [
                'en' => $partner_subscription_package->show_name,
                'bn' => $partner_subscription_package->show_name_bn
            ],
            'last_billing_date'          => $partner->last_billed_date ? $partner->last_billed_date->format('Y-m-d') : null,
            'next_billing_date'          => $partner->periodicBillingHandler()->nextBillingDate() ? $partner->periodicBillingHandler()->nextBillingDate()->format('Y-m-d') : null,
            'validity_remaining_in_days' => $partner->last_billed_date ? $partner->periodicBillingHandler()->remainingDay() : null,
            'is_auto_billing_activated'  => ($partner->auto_billing_activated) ? true : false,
            'balance'                    => [
                'wallet'                 => $wallet + $bonus_wallet,
                'refund'                 => $remaining,
                'minimum_wallet_balance' => $threshold
            ],
            'subscription_vat'           => SubscriptionStatics::getPartnerSubscriptionVat(),
            'popular_package_id'         => SubscriptionStatics::getPopularPackageId()
        ];

        return array_merge($data, SubscriptionStatics::getPackageStaticDiscount());
    }

    public function updateRenewSubscription(array $data, Partner $partner)
    {
        $partner->auto_billing_activated = isset($data['auto_billing_activated']) ? $data['auto_billing_activated'] : $partner->auto_billing_activated;
        $partner->subscription_renewal_warning = isset($data['subscription_renewal_warning']) ? $data['subscription_renewal_warning'] :  $partner->subscription_renewal_warning;
        $partner->renewal_warning_days = isset($data['renewal_warning_days']) ? $data['renewal_warning_days'] :  $partner->renewal_warning_days;
        return $partner->save();
    }

    /**
     * @param $partner_id
     * @return array
     */
    public function packageFeatureCount($partner_id)
    {
        /** @var PackageFeatureCount $packageFeatureCount */
        $packageFeatureCount = app(PackageFeatureCount::class);
        $features_count = $packageFeatureCount->setPartnerId($partner_id)->featuresCurrentCountList();

        if (! $features_count) {
            return [];
        }

        return [
            "topup" => $features_count->topup,
            "sms" => $features_count->sms,
            "delivery" => $features_count->delivery,
        ];
    }

    /**
     * @param $features_count
     * @return array[]
     */
    private function formatFeatureCountList($features_count): array
    {
        return [
            [
                "feature" => "??????-??????",
                "count" => $features_count['topup']
            ],
            [
                "feature" => "???????????? SMS",
                "count" => $features_count['sms']
            ],
            [
                "feature" => "???????????????????????? ??????????????????",
                "count" => $features_count['delivery']
            ]
        ];
    }
}