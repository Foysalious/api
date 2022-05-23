<?php namespace App\Sheba\Partner;

use Sheba\Dal\PartnerPackageFeatureCounter\EloquentImplementation as PartnerPackageFeatureCounter;
use Sheba\ModificationFields;

class FeatureCounter
{
    use ModificationFields;


    private $count;
    private $partnerPackageFeatureCounter;

    public function __construct(PartnerPackageFeatureCounter $partnerPackageFeatureCounter)
    {
        $this->partnerPackageFeatureCounter = $partnerPackageFeatureCounter;
    }



    /**
     * @param $count
     * @return $this
     */
    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    }

    /**
     * @param $feature
     * @param $partner
     * @return mixed
     */
    public function getCurrentCount($feature, $partner)
    {
        return $this->allFeaturesCount($partner)->{$feature};
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
        $feature_count = $features_count_model->{$feature};

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
     */
    public function decrementCount($feature, $partner, $count)
    {
        $features_count_model = $this->allFeaturesCount($partner);
        $feature_count = $features_count_model->{$feature};

        if ($feature_count >= $count) {
            $updated_count = $feature_count - $count;
            $data[$feature] = $updated_count;
            $features_count_model->update($this->withUpdateModificationField($data));
            return $updated_count;
        }
        return "you don't have sufficient {$feature}";

    }

    /**
     * @param $partner
     * @return mixed
     */
    private function allFeaturesCount($partner)
    {
        return $this->partnerPackageFeatureCounter->where('partner_id', $partner)->first();
    }

//    private function decrement($feature_count)
//    {
//        $updated_count = $feature_count - $this->count;
//        $data[$this->feature] = $updated_count;
//        $this->allFeaturesCount()->update($this->withUpdateModificationField($data));
//        return $updated_count;
//    }
}




