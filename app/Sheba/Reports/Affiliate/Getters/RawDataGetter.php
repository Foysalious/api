<?php namespace Sheba\Reports\Affiliate\Getters;

use Illuminate\Database\Eloquent\Builder;
use Sheba\Reports\Affiliate\Query;
use Sheba\Reports\Affiliate\Presenter;

class RawDataGetter extends Getter
{
    private $query;

    public function __construct(Query $query, Presenter $presenter)
    {
        parent::__construct($presenter);
        $this->query = $query;
    }

    /**
     * @return string
     */
    protected function getPresenterMethod()
    {
        return 'setAffiliate';
    }

    /**
     * @return Builder
     */
    protected function getQuery()
    {
        return $this->query->setTimeFrame($this->timeFrame)->build();
    }
}
