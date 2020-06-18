<?php namespace Sheba\AutoSpAssign;


use Sheba\AutoSpAssign\Sorting\Parameter\AvgRating;
use Sheba\AutoSpAssign\Sorting\Parameter\ComplainPercentage;
use Sheba\AutoSpAssign\Sorting\Parameter\Impression;
use Sheba\AutoSpAssign\Sorting\Parameter\Ita;
use Sheba\AutoSpAssign\Sorting\Parameter\MaxRevenue;
use Sheba\AutoSpAssign\Sorting\Parameter\Ota;
use Sheba\AutoSpAssign\Sorting\Parameter\PackageScore;
use Sheba\AutoSpAssign\Sorting\Parameter\ResourceAppUsage;

class SortingAlgorithm
{

    /**
     * @return array
     */
    public function getBaseParameters()
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

    public function getParameters()
    {
        return array_merge($this->getBaseParameters(),[new Impression()]);
    }

    public function getParametersForNewCustomer()
    {
        return array_merge($this->getBaseParameters());
    }
}