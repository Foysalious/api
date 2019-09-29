<?php namespace Sheba\Reports\Complain\Getters;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Sheba\Reports\Complain\Presenter;
use Sheba\Reports\ReportData;
use Illuminate\Http\Request;

abstract class Getter extends ReportData
{
    protected $presenter;

    public function __construct(Presenter $presenter)
    {
        $this->presenter = $presenter;
    }

    /**
     * @return Builder
     */
    protected abstract function getQuery();

    /**
     * @param $data
     * @return Collection
     */
    protected abstract function mapCustomerFirstOrder($data);

    /**
     * @param $item
     * @return mixed
     */
    protected abstract function mapForView($item);

    /**
     * @param Request $request
     * @return array
     */
    public function get(Request $request)
    {
        $data = $this->notLifetimeQuery($this->getQuery(), $request->all())->get();
        $data = $this->mapCustomerFirstOrder($data);
        return $data->map(function ($item) {
            return $this->mapForView($item);
        })->toArray();
    }

}