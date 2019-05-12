<?php namespace Sheba\Reports\Affiliate\Getters;

use Illuminate\Database\Eloquent\Builder;
use Sheba\Reports\Affiliate\Presenter;
use Sheba\Reports\ReportData;
use Illuminate\Http\Request;
use Sheba\Helpers\TimeFrame;

abstract class Getter extends ReportData
{
    protected $presenter;
    /** @var TimeFrame */
    protected $timeFrame;

    public function __construct(Presenter $presenter)
    {
        $this->presenter = $presenter;
    }

    /**
     * @return Builder
     */
    protected abstract function getQuery();

    /**
     * @return string
     */
    protected abstract function getPresenterMethod();

    /**
     * @param Request $request
     * @return mixed
     */
    public function get(Request $request)
    {
        list($from_date, $to_date) = $this->getStartEnd($request);
        $this->timeFrame = new TimeFrame($from_date, $to_date);
        $presenter_method = $this->getPresenterMethod();
        return $this->getQuery()->get()->map(function ($item) use ($presenter_method) {
            return $this->presenter->$presenter_method($item)->getForView();
        })->toArray();
    }
}
