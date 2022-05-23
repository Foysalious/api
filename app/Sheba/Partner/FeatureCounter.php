<?php namespace App\Sheba\Partner;

use Exception;
use Sheba\Dal\PartnerPackageFeatureCounter\EloquentImplementation as PartnerPackageFeatureCounter;
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
     */
    public function getCurrentCount($feature, $partner)
    {
        return $this->allFeaturesCount($partner)[$feature];
    }

    /**
     * @param $feature
     * @param $partner
     * @param $count
     * @return mixed
     */
    public function incrementCount($feature, $partner, $count)
    {
        $features_count_model = $this->allFeaturesCount($partner);
        $feature_count = $features_count_model[$feature];

        $updated_count = $feature_count + $count;
        $data[$feature] = $updated_count;
        $features_count_model->update($this->withUpdateModificationField($data));
        return $updated_count;
    }

    /**
     * @param $feature
     * @param $partner
     * @param $count
     * @return string
     * @throws Exception
     */
    public function decrementCount($feature, $partner, $count)
    {
        $features_count_model = $this->allFeaturesCount($partner);
        $feature_count = $features_count_model[$feature];

        if ($feature_count >= $count) {
            $updated_count = $feature_count - $count;
            $data[$feature] = $updated_count;
            $features_count_model->update($this->withUpdateModificationField($data));
            return $updated_count;
        } else {
            $message = "you don't have sufficient {$feature}";
            throw new Exception($message);
        }
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




