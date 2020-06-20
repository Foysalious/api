<?php namespace Sheba\AutoSpAssign\Sorting\Strategy;


use Sheba\AutoSpAssign\EligiblePartner;
use Sheba\AutoSpAssign\Sorting\Parameter\AvgRating;
use Sheba\AutoSpAssign\Sorting\Parameter\ComplainPercentage;
use Sheba\AutoSpAssign\Sorting\Parameter\Ita;
use Sheba\AutoSpAssign\Sorting\Parameter\MaxRevenue;
use Sheba\AutoSpAssign\Sorting\Parameter\Ota;
use Sheba\AutoSpAssign\Sorting\Parameter\PackageScore;
use Sheba\AutoSpAssign\Sorting\Parameter\Parameter;
use Sheba\AutoSpAssign\Sorting\Parameter\ResourceAppUsage;

class Best implements Strategy
{
    private $maxRevenue;
    private $minRevenue;
    private $minRating;
    private $maxRating;

    /**
     * @return Parameter[]
     */
    public function getNormalizedParameters()
    {
        return [
            new ComplainPercentage(),
            new Ita(),
            new Ota(),
            new PackageScore(),
            new ResourceAppUsage()
        ];
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
     * @return EligiblePartner[]|void
     */
    public function sort($partners)
    {
        $max_rating = $max_revenue = $min_revenue = $min_rating = null;
        foreach ($partners as $partner) {
            $max_revenue = $partner->getMaxRevenue() > $max_revenue ? $partner->getMaxRevenue() : $max_revenue;
            $max_rating = $partner->getAvgRating() > $max_rating ? $partner->getAvgRating() : $max_rating;
            $min_revenue = $partner->getMaxRevenue() < $min_revenue || !$min_revenue ? $partner->getMaxRevenue() : $min_revenue;
            $min_rating = $partner->getAvgRating() < $min_rating || !$min_rating ? $partner->getAvgRating() : $min_rating;
        }
        $this->setMaxRevenue($max_revenue)->setMinRevenue($min_revenue)->setMaxRating($max_rating)->setMinRating($min_rating);
        foreach ($partners as $partner) {
            $score = $this->getScore($partner) + $this->getScoreForNonNormalizedParams($partner);
            $partner->setScore($score);
        }
        return collect($partners)->sortByDesc('score')->toArray();
    }

    private function getScore(EligiblePartner $partner)
    {
        $score = 0;
        foreach ($this->getNormalizedParameters() as $param) {
            dump($param->setPartner($partner)->getScore(),$param);
            $score += $param->setPartner($partner)->getScore();
        }
        return $score;
    }

    private function getScoreForNonNormalizedParams(EligiblePartner $partner)
    {
        $score = 0;
        $param = new MaxRevenue();
        $rating_param = new AvgRating();
        $score += $param->setMaxValue($this->maxRevenue)->setMinValue($this->minRevenue)->setPartner($partner)->getScore();
        $score += $rating_param->setMaxValue($this->maxRating)->setMinValue($this->minRating)->setPartner($partner)->getScore();
        return $score;
    }

}