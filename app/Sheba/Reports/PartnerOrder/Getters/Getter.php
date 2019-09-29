<?php namespace Sheba\Reports\PartnerOrder\Getters;

use App\Models\PartnerOrder;
use App\Models\PartnerOrderReport;
use Illuminate\Http\Request;
use Sheba\Helpers\TimeFrame;
use Sheba\Reports\PartnerOrder\Presenter;
use Sheba\Reports\PartnerOrder\Repositories\Repository;
use Sheba\Reports\ReportData;

abstract class Getter extends ReportData
{
    protected $repo;
    protected $presenter;
    protected $field = "created_at";

    public function __construct(Repository $repo, Presenter $presenter)
    {
        $this->repo = $repo;
        $this->presenter = $presenter;
    }

    public function setDateFilterField($field)
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function get(Request $request)
    {
        return $this->repo->setPartner($request->partner_id)
            ->setTimeline(['field' => $this->field, 'timeline' => $request->all()])
            ->setCancelledDateRange(new TimeFrame($request->cancel_start_date, $request->cancel_end_date))
            ->setClosedDateRange(new TimeFrame($request->closed_start_date, $request->closed_end_date))
            ->get()->map(function ($item) {
                return $this->mapForView($item);
            })->toArray();
    }

    /**
     * @param PartnerOrder|PartnerOrderReport $item
     * @return array
     */
    protected abstract function mapForView($item);
}