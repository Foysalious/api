<?php namespace App\Sheba\Partner;

use Sheba\Dal\PartnerPackageFeatureCounter\EloquentImplementation as PartnerPackageFeatureCounter;
use Sheba\ModificationFields;

class PackageFeatureCount
{
    use ModificationFields;

    protected $partnerPackageFeatureCounter;

    public function __construct(PartnerPackageFeatureCounter $partnerPackageFeatureCounter)
    {
        $this->partnerPackageFeatureCounter = $partnerPackageFeatureCounter;
    }

    /**
     * @param $feature
     * @param $partner
     * @return mixed
     */
    public function featureCurrentCount($feature, $partner)
    {
        return $this->allFeaturesCount($partner)->$feature;
    }

    /**
     * @param $feature
     * @param $updated_count
     * @param $partner
     * @return bool
     */
    public function featureCountUpdate($feature, $updated_count, $partner): bool
    {
        $features_count = $this->allFeaturesCount($partner);
        $data[$feature] = $updated_count;
        $features_count->update($this->withUpdateModificationField($data));
        return true;
    }

    /**
     * @param $partner
     * @return mixed
     */
    private function allFeaturesCount($partner)
    {
        return $this->partnerPackageFeatureCounter->where('partner_id', $partner)->first();
    }
}