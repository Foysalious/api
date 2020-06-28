<?php namespace Sheba\AutoSpAssign\Sorting\Strategy;


use Sheba\AutoSpAssign\EligiblePartner;
use Sheba\AutoSpAssign\Sorting\Parameter\AvgRating;
use Sheba\AutoSpAssign\Sorting\Parameter\ComplainPercentage;
use Sheba\AutoSpAssign\Sorting\Parameter\Impression;
use Sheba\AutoSpAssign\Sorting\Parameter\Ita;
use Sheba\AutoSpAssign\Sorting\Parameter\MaxRevenue;
use Sheba\AutoSpAssign\Sorting\Parameter\Ota;
use Sheba\AutoSpAssign\Sorting\Parameter\PackageScore;
use Sheba\AutoSpAssign\Sorting\Parameter\Parameter;
use Sheba\AutoSpAssign\Sorting\Parameter\ResourceAppUsage;

class Basic implements Strategy
{

    private $maxRevenue;
    private $minRevenue;
    private $minRating;
    private $maxRating;
    private $maxImpression;
    private $minImpression;

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

    public function setMaxImpression($maxImpression)
    {
        $this->maxImpression = $maxImpression;
        return $this;
    }

    public function setMinImpression($minImpression)
    {
        $this->minImpression = $minImpression;
        return $this;
    }

    /**
     * @param EligiblePartner[] $partners
     * @return EligiblePartner[]|void
     */
    public function sort($partners)
    {
        $max_rating = $max_revenue = $min_revenue = $min_rating = $max_impression = $min_impression = null;
        foreach ($partners as $partner) {
            $max_revenue = $partner->getMaxRevenue() > $max_revenue ? $partner->getMaxRevenue() : $max_revenue;
            $max_rating = $partner->getAvgRating() > $max_rating ? $partner->getAvgRating() : $max_rating;
            $max_impression = $partner->getImpressionCount() > $max_impression ? $partner->getImpressionCount() : $max_impression;
            $min_revenue = $partner->getMaxRevenue() < $min_revenue || !$min_revenue ? $partner->getMaxRevenue() : $min_revenue;
            $min_rating = $partner->getAvgRating() < $min_rating || !$min_rating ? $partner->getAvgRating() : $min_rating;
            $min_impression = $partner->getImpressionCount() < $min_impression || !$min_impression ? $partner->getAvgRating() : $min_impression;
        }
        $this->setMaxRevenue($max_revenue)->setMinRevenue($min_revenue)->setMaxRating($max_rating)->setMinRating($min_rating)
            ->setMaxImpression($max_impression)->setMinImpression($min_impression);
        $newSp = $oldSp = [];
        foreach ($partners as $partner) {
            $score = $this->getScore($partner) + $this->getScoreForNonNormalizedParams($partner);
            $partner->setScore($score);
            if ($partner->isNew()) array_push($newSp, $partner);
            else array_push($oldSp, $partner);
        }
        $newSp = collect($newSp)->sortByDesc('score')->toArray();
        $oldSp = collect($oldSp)->sortByDesc('score')->toArray();
        return array_merge($newSp, $oldSp);
    }

    private function getScore(EligiblePartner $partner)
    {
        $score = 0;
        foreach ($this->getNormalizedParameters() as $param) {
            $score += $param->setPartner($partner)->getScore();
        }
        return $score;
    }

    private function getScoreForNonNormalizedParams(EligiblePartner $partner)
    {
        $score = 0;
        $param = new MaxRevenue();
        $rating_param = new AvgRating();
        $impression = new Impression();
        $score += $param->setMaxValue($this->maxRevenue)->setMinValue($this->minRevenue)->setPartner($partner)->getScore();
        $score += $rating_param->setMaxValue($this->maxRating)->setMinValue($this->minRating)->setPartner($partner)->getScore();
        $score += $impression->setMaxValue($this->maxImpression)->setMinValue($this->minImpression)->setPartner($partner)->getScore();
        return $score;
    }

}