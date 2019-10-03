<?php namespace Sheba\Reports\Affiliation\Getters;

use App\Models\AffiliationReport;

class GeneratedDataGetter extends Getter
{
    /**
     * @param AffiliationReport $item
     * @return array
     */
    protected function mapForView($item)
    {
        return $this->presenter->setAffiliationReport($item)->getForView();
    }

    protected function getQuery()
    {
       return AffiliationReport::query();
    }
}