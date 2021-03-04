<?php namespace Sheba\AutoSpAssign\Sorting\Strategy;


use Sheba\AutoSpAssign\EligiblePartner;
use Sheba\AutoSpAssign\Sorting\Parameter\AvgRating;
use Sheba\AutoSpAssign\Sorting\Parameter\ComplainPercentage;
use Sheba\AutoSpAssign\Sorting\Parameter\Impression;
use Sheba\AutoSpAssign\Sorting\Parameter\InTimeAcceptance;
use Sheba\AutoSpAssign\Sorting\Parameter\MaxRevenue;
use Sheba\AutoSpAssign\Sorting\Parameter\OnTimeArrival;
use Sheba\AutoSpAssign\Sorting\Parameter\PackageScore;
use Sheba\AutoSpAssign\Sorting\Parameter\Parameter;
use Sheba\AutoSpAssign\Sorting\Parameter\ResourceAppUsage;

class Basic extends Strategy
{
    private $maxImpression;
    private $minImpression;

    /**
     * @param EligiblePartner[] $partners
     */
    protected function initiateMinMaxForNonNormalizedParams($partners)
    {
        parent::initiateMinMaxForNonNormalizedParams($partners);

        $this->maxImpression = $this->minImpression = (double)$partners[0]->getImpressionCount();
    }

    /**
     * @param EligiblePartner $partner
     */
    protected function updateMinMaxOfNonNormalizedParamsForAPartner(EligiblePartner $partner)
    {
        parent::updateMinMaxOfNonNormalizedParamsForAPartner($partner);

        $this->maxImpression = (double)$partner->getImpressionCount() > $this->maxImpression ? $partner->getImpressionCount() : $this->maxImpression;
        $this->minImpression = (double)$partner->getImpressionCount() < $this->minImpression ? $partner->getAvgRating() : $this->minImpression;
    }

    /**
     * @param EligiblePartner[] $partners
     * @return EligiblePartner[]|void
     */
    public function sort($partners)
    {
        $this->setMinMaxForNonNormalizedParams($partners);

        $new_sp = $old_sp = [];
        foreach ($partners as $partner) {
            $score = $this->calculateScoreWithImpression($partner, $this->getQualityScore($partner));
            $partner->setScore($score);
            if ($partner->isNew()) array_push($new_sp, $partner);
            else array_push($old_sp, $partner);
        }

        $new_sp = collect($new_sp)->sortByDesc('score')->toArray();
        $old_sp = collect($old_sp)->sortByDesc('score')->toArray();
        return array_merge($new_sp, $old_sp);
    }

    private function calculateScoreWithImpression(EligiblePartner $partner, $quality_score)
    {
        $impression = new Impression();
        $impression_weighted_score = $impression->setMaxValue($this->maxImpression)->setMinValue($this->minImpression)
            ->setPartner($partner)->setCategoryId($this->categoryId)->getScore();
        $impression_weight = $impression->getWeightInScaleOf1();
        return $quality_score * (1 - $impression_weight) + $impression_weighted_score;
    }
}
