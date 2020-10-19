<?php namespace Sheba\AutoSpAssign\Sorting\Strategy;

use Sheba\AutoSpAssign\EligiblePartner;
use Sheba\AutoSpAssign\Sorting\Parameter\AvgRating;
use Sheba\AutoSpAssign\Sorting\Parameter\ComplainPercentage;
use Sheba\AutoSpAssign\Sorting\Parameter\InTimeAcceptance;
use Sheba\AutoSpAssign\Sorting\Parameter\MaxRevenue;
use Sheba\AutoSpAssign\Sorting\Parameter\OnTimeArrival;
use Sheba\AutoSpAssign\Sorting\Parameter\PackageScore;
use Sheba\AutoSpAssign\Sorting\Parameter\Parameter;
use Sheba\AutoSpAssign\Sorting\Parameter\ResourceAppUsage;

class Best extends Strategy
{
    /**
     * @param EligiblePartner[] $partners
     * @return EligiblePartner[]|void
     */
    public function sort($partners)
    {
        $this->setMinMaxForNonNormalizedParams($partners);

        foreach ($partners as $partner) {
            $partner->setScore($this->getQualityScore($partner));
        }

        return collect($partners)->sortByDesc('score')->toArray();
    }
}