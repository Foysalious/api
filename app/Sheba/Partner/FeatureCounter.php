<?php namespace App\Sheba\Partner;

use App\Exceptions\PartnerDataNotFoundException;
use DB;
use Exception;
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
     * @throws Exception
     */
    public function getCurrentCount($feature, $partner)
    {
        $features_count_model = $this->allFeaturesCount($partner);
        $this->isPartnerDataAvailable($features_count_model);

        return $features_count_model[$feature];
    }

    /**
     * @param $partner
     * @return mixed
     */
    public function getAllFeaturesCurrentCount($partner)
    {
        return $this->allFeaturesCount($partner);
    }

    /**
     * @param $feature
     * @param $partner
     * @param $count
     * @return bool
     * @throws Exception
     */
    public function isEligible($feature, $partner, $count)
    {
        $features_count_model = $this->allFeaturesCount($partner);
        $this->isPartnerDataAvailable($features_count_model);
        $feature_count = $features_count_model[$feature];
        return $feature_count >= $count;
    }

    /**
     * @param $feature
     * @param $partner
     * @param $count
     * @return mixed
     * @throws Exception
     */
    public function incrementCount($feature, $partner, $count)
    {
        $features_count_model = $this->allFeaturesCount($partner);
        $this->isPartnerDataAvailable($features_count_model);
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
     * @throws Exception
     */
    public function decrementCount($feature, $partner, $count)
    {
        $features_count_model = $this->allFeaturesCount($partner);
        $this->isPartnerDataAvailable($features_count_model);
        $feature_count = $features_count_model[$feature];
        $updated_count = $feature_count - $count;

        if ($updated_count >= 0) {
            try {
                DB::transaction(function () use($updated_count, $feature, $features_count_model) {

                    $data[$feature] = $updated_count;
                    $features_count_model->update($this->withUpdateModificationField($data));
                });

                return ucfirst($feature) . " count has updated successfully!";
            } catch (QueryException $e) {
                return $e->getMessage();
            }
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

    /**
     * @param $features_count_model
     * @return bool
     * @throws Exception
     */
    private function isPartnerDataAvailable($features_count_model)
    {
        if (! $features_count_model) {
            throw new PartnerDataNotFoundException();
        }
        return true;
    }

}




