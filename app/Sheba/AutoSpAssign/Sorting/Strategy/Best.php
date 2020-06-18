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
    /**
     * @return Parameter[]
     */
    public function getParameters()
    {
        return [
            new AvgRating(),
            new ComplainPercentage(),
            new Ita(),
            new MaxRevenue(),
            new Ota(),
            new PackageScore(),
            new ResourceAppUsage()
        ];
    }

    /**
     * @param EligiblePartner[] $partners
     * @return EligiblePartner[]|void
     */
    public function sort($partners)
    {
        dd((collect($partners)));
        foreach ($partners as $partner) {
            $partner->setScore($this->getScore($partner));
        }
        dd($partners);
    }

    private function getScore(EligiblePartner $partner)
    {
        $score = 0;
        foreach ($this->getParameters() as $params) {
            $score += $params->setPartner($partner)->getScore();
        }
        return $score;
    }
}