<?php namespace Sheba\Reports\Complain\Getters;

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

    protected abstract function getQuery();

    protected abstract function mapCustomerFirstOrder($data);

    protected abstract function mapForView($item);

    public function get(Request $request)
    {
        $data = $this->notLifetimeQuery($this->getQuery(), $request->all())->get();
        $data = $this->mapCustomerFirstOrder($data);
        return $data->map(function ($item) {
            return $this->mapForView($item);
        })->toArray();
    }

}