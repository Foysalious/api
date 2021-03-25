<?php namespace Sheba\AutoSpAssign\Sorting\Strategy;

use Sheba\AutoSpAssign\EligiblePartner;
use Sheba\AutoSpAssign\Sorting\Parameter\AvgRating;
use Sheba\AutoSpAssign\Sorting\Parameter\Commission;
use Sheba\AutoSpAssign\Sorting\Parameter\ComplainPercentage;
use Sheba\AutoSpAssign\Sorting\Parameter\InTimeAcceptance;
use Sheba\AutoSpAssign\Sorting\Parameter\MaxRevenue;
use Sheba\AutoSpAssign\Sorting\Parameter\OnTimeArrival;
use Sheba\AutoSpAssign\Sorting\Parameter\PackageScore;
use Sheba\AutoSpAssign\Sorting\Parameter\Parameter;
use Sheba\AutoSpAssign\Sorting\Parameter\ResourceAppUsage;

abstract class Strategy
{
    protected $maxRevenue;
    protected $minRevenue;
    protected $minRating;
    protected $maxRating;
    protected $categoryId;

    /**
     * @param $category_id
     * @return Strategy
     */
    public function setCategoryId($category_id)
    {
        $this->categoryId = $category_id;
        return $this;
    }

    public function setMaxRevenue($maxRevenue)
    {
        $this->maxRevenue = $maxRevenue;
        return $this;
    }

    public function setMinRevenue($minRevenue)
    {
        $this->minRevenue = $minRevenue;
        return $this;
    }

    public function setMinRating($minRating)
    {
        $this->minRating = $minRating;
        return $this;
    }

    public function setMaxRating($maxRating)
    {
        $this->maxRating = $maxRating;
        return $this;
    }

    /**
     * @param EligiblePartner[] $partners
     * @return EligiblePartner[]
     */
    abstract public function sort($partners);

    /**
     * @return Parameter[]
     */
    public function getNormalizedParameters()
    {
        return [
            new ComplainPercentage(),
            new InTimeAcceptance(),
            new OnTimeArrival(),
            new PackageScore(),
            new ResourceAppUsage(),
            new Commission()
        ];
    }

    /**
     * @param EligiblePartner[] $partners
     */
    protected function initiateMinMaxForNonNormalizedParams($partners)
    {
        $this->maxRating = $this->minRating = (double)$partners[0]->getAvgRating();
        $this->maxRevenue = $this->minRevenue = (double)$partners[0]->getMaxRevenue();
    }

    /**
     * @param EligiblePartner $partner
     */
    protected function updateMinMaxOfNonNormalizedParamsForAPartner(EligiblePartner $partner)
    {
        $this->maxRevenue = ((double)$partner->getMaxRevenue() > $this->maxRevenue) ? $partner->getMaxRevenue() : $this->maxRevenue;
        $this->maxRating = ((double)$partner->getAvgRating() > $this->maxRating) ? $partner->getAvgRating() : $this->maxRating;
        $this->minRevenue = (double)$partner->getMaxRevenue() < $this->minRevenue ? $partner->getMaxRevenue() : $this->minRevenue;
        $this->minRating = (double)$partner->getAvgRating() < $this->minRating ? $partner->getAvgRating() : $this->minRating;
    }

    /**
     * @param EligiblePartner[] $partners
     * @return void
     */
    protected function setMinMaxForNonNormalizedParams($partners)
    {
        $this->initiateMinMaxForNonNormalizedParams($partners);
        foreach ($partners as $key => $partner) {
            $this->updateMinMaxOfNonNormalizedParamsForAPartner($partner);
        }
    }

    protected function getScoreForNonNormalizedParams(EligiblePartner $partner)
    {
        $score = 0;
        $param = new MaxRevenue();
        $rating_param = new AvgRating();
        $score += $param->setMaxValue($this->maxRevenue)->setMinValue($this->minRevenue)->setPartner($partner)->setCategoryId($this->categoryId)->getScore();
        $score += $rating_param->setMaxValue($this->maxRating)->setMinValue($this->minRating)->setPartner($partner)->setCategoryId($this->categoryId)->getScore();
        return $score;
    }

    protected function getScoreForNormalizedParams(EligiblePartner $partner)
    {
        $score = 0;
        foreach ($this->getNormalizedParameters() as $param) {
            $score += $param->setPartner($partner)->setCategoryId($this->categoryId)->getScore();
        }
        return $score;
    }

    protected function getQualityScore(EligiblePartner $partner)
    {
        return $this->getScoreForNormalizedParams($partner) + $this->getScoreForNonNormalizedParams($partner);
    }
}
