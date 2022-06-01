<?php namespace App\Sheba\Partner;

use Exception;

class PackageFeatureCount
{
    const TOPUP = 'topup';
    const SMS = 'sms';
    const DELIVERY = 'delivery';

    private $partner;
    private $feature;
    private $featureCounter;

    public function __construct(FeatureCounter $featureCounter)
    {
        $this->featureCounter = $featureCounter;
    }

    /**
     * @param $partner_id
     * @return $this
     */
    public function setPartnerId($partner_id)
    {
        $this->partner = $partner_id;
        return $this;
    }

    /**
     * @param $feature
     * @return $this
     * @throws Exception
     */
    public function setFeature($feature)
    {
        $this->feature = strtolower($feature);
        $this->validateFeatureName($this->feature);

        return $this;
    }

    /**
     * @return mixed
     */
    public function featureCurrentCount()
    {
        return $this->featureCounter->getCurrentCount($this->feature, $this->partner);
    }

    /**
     * @return mixed
     */
    public function featuresCurrentCountList()
    {
        return $this->featureCounter->getAllFeaturesCurrentCount($this->partner);
    }

    /**
     * @param int $count
     * @return bool
     * @throws Exception
     */
    public function isEligible(int $count = 1): bool
    {
        return $this->featureCounter->isEligible($this->feature, $this->partner, $count);
    }

    /**
     * @param int $count
     * @return string
     * @throws Exception
     */
    public function incrementFeatureCount(int $count=1)
    {
        return $this->featureCounter->incrementCount($this->feature, $this->partner, $count);
    }

    /**
     * @param int $count
     * @return string
     * @throws Exception
     */
    public function decrementFeatureCount(int $count=1)
    {
        return $this->featureCounter->decrementCount($this->feature, $this->partner, $count);
    }

    /**
     * @param $feature
     * @return bool
     * @throws Exception
     */
    private function validateFeatureName($feature)
    {
        if ($feature == self::DELIVERY || $feature == self::SMS || $feature == self::TOPUP) {
            return true;
        } else {
            throw new Exception('You have tried with incorrect feature name');
        }
    }
}