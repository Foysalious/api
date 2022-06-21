<?php namespace App\Sheba\Partner;

use App\Exceptions\PartnerDataNotFoundException;
use DB;
use Sheba\Dal\PartnerPackageFeatureCounter\Contract as PartnerPackageFeatureCounter;
use Sheba\ModificationFields;

class FeatureCounter
{
    use ModificationFields;

    private $partnerPackageFeatureCounter;

    public function __construct(PartnerPackageFeatureCounter $partnerPackageFeatureCounter)
    {
        $this->partnerPackageFeatureCounter = $partnerPackageFeatureCounter;
    }

    /**
     * @param $feature
     * @param $partner
     * @return mixed
     * @throws PartnerDataNotFoundException
     */
    public function getCurrentCount($feature, $partner)
    {
        $features_count_model = $this->partnerPackageFeatureCounter->getFeatureCountByPartner($partner);

        if (!$features_count_model) throw new PartnerDataNotFoundException();

        return $features_count_model[$feature];
    }

    /**
     * @param $partner
     * @return \Sheba\Dal\PartnerPackageFeatureCounter\Model|null
     */
    public function getAllFeaturesCurrentCount($partner)
    {
        return $this->partnerPackageFeatureCounter->getFeatureCountByPartner($partner);
    }

    /**
     * @param $feature
     * @param $partner
     * @param $count
     * @return bool
     */
    public function isEligible($feature, $partner, $count)
    {
        $features_count = $this->partnerPackageFeatureCounter->getFeatureCountByPartner($partner);

        if (!$features_count) return false;

        return $features_count->isEligible($feature, $count);
    }

    /**
     * @param $feature
     * @param $partner
     * @param $count
     * @throws PartnerDataNotFoundException
     */
    public function incrementCount($feature, $partner, $count)
    {
        $features_count = $this->partnerPackageFeatureCounter->getFeatureCountByPartner($partner);

        if (!$features_count) throw new PartnerDataNotFoundException();

        if ($features_count->isUnlimited($feature)) return;

        $this->partnerPackageFeatureCounter->update($features_count, [
            $feature => $features_count->getCount($feature) + $count
        ]);
    }

    /**
     * @param $feature
     * @param $partner
     * @param $count
     * @throws PartnerDataNotFoundException
     */
    public function decrementCount($feature, $partner, $count)
    {
        $features_count = $this->partnerPackageFeatureCounter->getFeatureCountByPartner($partner);

        if (!$features_count) throw new PartnerDataNotFoundException();

        if ($features_count->isUnlimited($feature)) return;

        $updated_count = $features_count->getCount($feature) - $count;

        if ($updated_count < 0) return;

        $this->partnerPackageFeatureCounter->update($features_count, [
            $feature => $updated_count
        ]);
    }
}




