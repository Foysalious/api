<?php namespace App\Sheba\Partner;

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
     */
    public function setFeature($feature)
    {
        $this->feature = $feature;
        return $this;
    }

    /**
     * @return mixed
     */
    public function featureCurrentCount()
    {
        if ($this->feature == self::DELIVERY || $this->feature == self::SMS || $this->feature == self::TOPUP) {
            return $this->featureCounter->getCurrentCount($this->feature, $this->partner);
        }
        return 'Please enter correct feature name';
    }

    /**
     * @param $count
     * @return string
     */
    public function incrementFeatureCount($count)
    {
        if ($this->feature == self::DELIVERY || $this->feature == self::SMS || $this->feature == self::TOPUP) {
            return $this->featureCounter->incrementCount($this->feature, $this->partner, $count);
        }
        return 'Please enter correct feature name';
    }

    /**
     * @param $count
     * @return string
     */
    public function decrementFeatureCount($count)
    {
        if ($this->feature == self::DELIVERY || $this->feature == self::SMS || $this->feature == self::TOPUP) {
            return $this->featureCounter->decrementCount($this->feature, $this->partner, $count);
        }
        return 'Please enter correct feature name';
    }
}