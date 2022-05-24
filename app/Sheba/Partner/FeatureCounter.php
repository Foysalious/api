<?php namespace App\Sheba\Partner;

use DB;
use Illuminate\Database\QueryException;
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
     * @return bool
     */
    public function isEligible($feature, $partner, $count)
    {
        $features_count_model = $this->allFeaturesCount($partner);
        $feature_count = $features_count_model[$feature];
        return $feature_count >= $count;
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

        try {
            DB::transaction(function () use($feature_count, $count, $feature, $features_count_model) {
                $updated_count = $feature_count + $count;
                $data[$feature] = $updated_count;
                $features_count_model->update($this->withUpdateModificationField($data));
            });

            return ucfirst($feature) . " count has updated successfully!";
        } catch (QueryException $e) {
            return $e->getMessage();
        }

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
        $feature_count = $features_count_model[$feature];

        try {
            DB::transaction(function () use($feature_count, $count, $feature, $features_count_model) {
                $updated_count = $feature_count - $count;
                $data[$feature] = $updated_count;
                $features_count_model->update($this->withUpdateModificationField($data));
            });

            return ucfirst($feature) . " count has updated successfully!";
        } catch (QueryException $e) {
            return $e->getMessage();
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




