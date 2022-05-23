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
     * @param $partner
     * @return $this
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param $feature
     * @return $this
     * @throws Exception
     */
    public function setFeature($feature)
    {
        $this->feature = $feature;
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
     * @param $count
     * @return string
     */
    public function incrementFeatureCount($count)
    {
        return $this->featureCounter->incrementCount($this->feature, $this->partner, $count);
    }

    /**
     * @param $count
     * @return string
     */
    public function decrementFeatureCount($count)
    {
        return $this->featureCounter->decrementCount($this->feature, $this->partner, $count);
    }

    private function validateFeatureName($feature)
    {
        if ($feature == self::DELIVERY || $feature == self::SMS || $feature == self::TOPUP) {
            return true;
        } else {
            throw new Exception('You have tried with incorrect feature name');
        }
    }
}