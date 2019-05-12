<?php namespace Sheba\Reports\Affiliate\Getters;

use Illuminate\Database\Query\Builder;
use Sheba\Dal\AffiliateReport\AffiliateReport;

class GeneratedDataGetter extends Getter
{
    /**
     * @return string
     */
    protected function getPresenterMethod()
    {
        return 'setAffiliateReport';
    }

    /**
     * @return Builder
     */
    protected function getQuery()
    {
        return AffiliateReport::whereBetween('created_at', $this->timeFrame->getArray());
    }
}
