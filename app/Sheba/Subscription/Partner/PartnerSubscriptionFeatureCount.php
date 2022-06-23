<?php namespace Sheba\Subscription\Partner;

use App\Models\PartnerSubscriptionPackage;
use Sheba\Dal\PartnerPackageFeatureCounter\Contract as PartnerPackageFeatureCounter;
use Sheba\ModificationFields;

class PartnerSubscriptionFeatureCount
{
    use ModificationFields;

    /**
     * @param $partner
     * @param $package
     */
    public function updateFeatureCounts($partner, $packageId)
    {
        $partnerPackageFeatureCounter = app(PartnerPackageFeatureCounter::class);
        $package = PartnerSubscriptionPackage::where('id', $packageId)->first();

        $features_count = $partnerPackageFeatureCounter->getFeatureCountByPartner($partner->id);
        $feature_count_limit = json_decode($package->feature_count_limit);

        if (!$features_count) {
            $data = [
                'partner_id' => $partner->id,
                'topup' => $feature_count_limit->topup,
                'sms' => $feature_count_limit->sms,
                'delivery' => $feature_count_limit->sdelivery,
            ];

            $features_count->create($this->withCreateModificationField($data));
        } else {
            $data = [
                'topup' => $this->featureCountData($features_count, $feature_count_limit, 'topup'),
                'sms' => $this->featureCountData($features_count, $feature_count_limit, 'sms'),
                'delivery' => $this->featureCountData($features_count, $feature_count_limit, 'delivery'),
            ];

            $features_count->update($this->withUpdateModificationField($data));
        }
    }

    /**
     * @param $features_count
     * @param $feature_count_limit
     * @param $feature
     * @return mixed
     */
    private function featureCountData($features_count, $feature_count_limit, $feature)
    {
        $name_conversion = config('subscription_package_features.name_conversion');

        $count = $this->checkUnlimited($features_count, $feature_count_limit, $feature, $name_conversion) ? 'unlimited' : $features_count->topup + $feature_count_limit->{$name_conversion[$feature]};

        return $count;
    }

    /**
     * @param $features_count
     * @param $feature_count_limit
     * @param $feature
     * @param $name_conversion
     * @return bool
     */
    private function checkUnlimited($features_count, $feature_count_limit, $feature, $name_conversion)
    {
        return (gettype($features_count->$feature) == 'string' && $features_count->$feature == 'unlimited') ||
            (gettype($feature_count_limit->{$name_conversion[$feature]}) == 'string' && $feature_count_limit->{$name_conversion[$feature]} == 'unlimited');
    }
}