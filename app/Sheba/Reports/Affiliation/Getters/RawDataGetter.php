<?php namespace Sheba\Reports\Affiliation\Getters;

use App\Models\Affiliation;
use Sheba\Reports\Affiliation\Query;
use Sheba\Reports\Affiliation\Presenter;

class RawDataGetter extends Getter
{
    private $query;

    public function __construct(Query $query, Presenter $presenter)
    {
        parent::__construct($presenter);
        $this->query = $query;
    }

    /**
     * @param Affiliation $item
     * @return array
     */
    protected function mapForView($item)
    {
        return $this->presenter->setAffiliation($item)->getForView();
    }

    protected function getQuery()
    {
        return $this->query->build();
    }
}