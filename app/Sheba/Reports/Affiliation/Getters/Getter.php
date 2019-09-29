<?php namespace Sheba\Reports\Affiliation\Getters;

use App\Models\Affiliation;
use Illuminate\Database\Eloquent\Builder;
use Sheba\Reports\Affiliation\Presenter;
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
     * @param $item
     * @return mixed
     */
    protected abstract function mapForView($item);

    /**
     * @param Request $request
     * @return mixed
     */
    public function get(Request $request)
    {
        $data = $this->notLifetimeQuery($this->getQuery(), $request->all())->get();
        return $data->map(function ($item) {
            return $this->mapForView($item);
        })->toArray();
    }
}