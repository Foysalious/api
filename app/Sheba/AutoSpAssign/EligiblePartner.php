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
    public $score;

    public function getPackageId()
    {
        return $this->packageId;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setScore($score)
    {
        $this->score = $score;
        return $this;
    }

    public function setId($id)
    {
        $this->id = (int)$id;
        return $this;
    }

    public function setAvgRating($avgRating)
    {
        $this->avgRating = (double)$avgRating;
        return $this;
    }

    public function setComplainCount($complainCount)
    {
        $this->complainCount = (int)$complainCount;
        return $this;
    }

    public function setImpressionCount($impressionCount)
    {
        $this->impressionCount = (int)$impressionCount;
        return $this;
    }

    public function setItaCount($itaCount)
    {
        $this->itaCount = (int)$itaCount;
        return $this;
    }

    public function setMaxRevenue($maxRevenue)
    {
        $this->maxRevenue = (double)$maxRevenue;
        return $this;
    }

    public function setOtaCount($otaCount)
    {
        $this->otaCount = (int)$otaCount;
        return $this;
    }

    public function setPackageId($packageId)
    {
        $this->packageId = (int)$packageId;
        return $this;
    }

    public function setResourceAppUsageCount($resourceAppUsageCount)
    {
        $this->resourceAppUsageCount = (int)$resourceAppUsageCount;
        return $this;
    }


    public function setRecentServedJobCount($recentServedJobCount)
    {
        $this->recentServedJobCount = (int)$recentServedJobCount;
        return $this;
    }


    public function setLifetimeServedJobCount($lifetimeServedJobCount)
    {
        $this->lifetimeServedJobCount = (int)$lifetimeServedJobCount;
        return $this;
    }

    public function getAvgRating()
    {
        return $this->avgRating;
    }

    public function getMaxRevenue()
    {
        return $this->maxRevenue;
    }

    public function getIta()
    {
        if (!$this->recentServedJobCount) return 0;
        return ($this->itaCount / $this->recentServedJobCount) * 100;
    }

    public function getOta()
    {
        if (!$this->recentServedJobCount) return 0;
        return ($this->otaCount / $this->recentServedJobCount) * 100;
    }

    public function getComplainRatio()
    {
        if (!$this->recentServedJobCount) return 0;
        return ($this->complainCount / $this->recentServedJobCount) * 100;
    }

    public function getResourceAppUsageRatio()
    {
        if (!$this->recentServedJobCount) return 0;
        return ($this->resourceAppUsageCount / $this->recentServedJobCount) * 100;
    }

    public function getImpressionCount()
    {
        return $this->impressionCount;
    }

    public function isNew()
    {
        return $this->lifetimeServedJobCount <= 10;
    }

}