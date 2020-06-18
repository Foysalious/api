<?php namespace Sheba\AutoSpAssign;


class EligiblePartner
{
    private $id;
    private $avgRating;
    private $complainCount;
    private $impressionCount;
    private $itaCount;
    private $maxRevenue;
    private $otaCount;
    private $packageId;
    private $resourceAppUsageCount;
    private $recentServedJobCount;
    private $lifetimeServedJobCount;

    /**
     * @param mixed $id
     * @return EligiblePartner
     */
    public function setId($id)
    {
        $this->id = (int)$id;
        return $this;
    }

    /**
     * @param mixed $avgRating
     * @return EligiblePartner
     */
    public function setAvgRating($avgRating)
    {
        $this->avgRating = (double)$avgRating;
        return $this;
    }

    /**
     * @param mixed $complainCount
     * @return EligiblePartner
     */
    public function setComplainCount($complainCount)
    {
        $this->complainCount = (int)$complainCount;
        return $this;
    }

    /**
     * @param mixed $impressionCount
     * @return EligiblePartner
     */
    public function setImpressionCount($impressionCount)
    {
        $this->impressionCount = (int)$impressionCount;
        return $this;
    }

    /**
     * @param mixed $itaCount
     * @return EligiblePartner
     */
    public function setItaCount($itaCount)
    {
        $this->itaCount = (int)$itaCount;
        return $this;
    }

    /**
     * @param mixed $maxRevenue
     * @return EligiblePartner
     */
    public function setMaxRevenue($maxRevenue)
    {
        $this->maxRevenue = (double)$maxRevenue;
        return $this;
    }

    /**
     * @param mixed $otaCount
     * @return EligiblePartner
     */
    public function setOtaCount($otaCount)
    {
        $this->otaCount = (int)$otaCount;
        return $this;
    }

    /**
     * @param mixed $packageId
     * @return EligiblePartner
     */
    public function setPackageId($packageId)
    {
        $this->packageId = (int)$packageId;
        return $this;
    }

    /**
     * @param mixed $resourceAppUsageCount
     * @return EligiblePartner
     */
    public function setResourceAppUsageCount($resourceAppUsageCount)
    {
        $this->resourceAppUsageCount = (int)$resourceAppUsageCount;
        return $this;
    }

    /**
     * @param mixed $recentServedJobCount
     * @return EligiblePartner
     */
    public function setRecentServedJobCount($recentServedJobCount)
    {
        $this->recentServedJobCount = (int)$recentServedJobCount;
        return $this;
    }

    /**
     * @param mixed $lifetimeServedJobCount
     * @return EligiblePartner
     */
    public function setLifetimeServedJobCount($lifetimeServedJobCount)
    {
        $this->lifetimeServedJobCount = (int)$lifetimeServedJobCount;
        return $this;
    }

}