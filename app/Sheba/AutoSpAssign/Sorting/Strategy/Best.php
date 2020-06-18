<?php namespace Sheba\AutoSpAssign\Sorting\Strategy;


use Sheba\AutoSpAssign\EligiblePartner;
use Sheba\AutoSpAssign\Sorting\Parameter\AvgRating;
use Sheba\AutoSpAssign\Sorting\Parameter\ComplainPercentage;
use Sheba\AutoSpAssign\Sorting\Parameter\Ita;
use Sheba\AutoSpAssign\Sorting\Parameter\MaxRevenue;
use Sheba\AutoSpAssign\Sorting\Parameter\Ota;
use Sheba\AutoSpAssign\Sorting\Parameter\PackageScore;
use Sheba\AutoSpAssign\Sorting\Parameter\ResourceAppUsage;

class Best implements Strategy
{
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

    public function sort($partners)
    {
        foreach ($partners as $partner) {

        }
    }
    private function getScore(EligiblePartner $partner){
     
    }
}