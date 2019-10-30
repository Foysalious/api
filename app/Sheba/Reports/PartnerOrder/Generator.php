<?php namespace Sheba\Reports\PartnerOrder;

use App\Models\PartnerOrder;
use App\Models\PartnerOrderReport;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Sheba\Reports\PartnerOrder\Repositories\RawRepository;

class Generator
{
    private $presenter;
    private $repo;

    public function __construct(Presenter $presenter, RawRepository $repo)
    {
        $this->presenter = $presenter;
        $this->repo = $repo;
    }

    public function createOrUpdate(PartnerOrder $partner_order)
    {
        if ($report = PartnerOrderReport::find($partner_order->id)) {
            $report->update($this->getReportData($partner_order));
        } else {
            $this->create($partner_order);
        }
    }

    public function create(PartnerOrder $partner_order)
    {
        return PartnerOrderReport::create($this->getReportData($partner_order));
    }

    public function refresh($skip = 0)
    {
        $limit = 500;
        $batch = (int)ceil(PartnerOrder::count() / $limit);

        $i = 0;
        if ($skip) {
            $this->createOrUpdateMultipleByLimitOffset($limit - $skip % $limit, $skip);
            $i = (int)ceil($skip / $limit);
        } else {
            PartnerOrderReport::truncate();
        }

        for (; $i < $batch; $i++) {
            $this->createOrUpdateMultipleByLimitOffset($limit, $limit * $i);
        }
    }

    public function createOrUpdateMultipleByLimitOffset($limit, $offset)
    {
        $this->createOrUpdateMultiple($this->repo->setLimitOffset($limit, $offset)->get());
    }

    public function createOrUpdateMultipleById(array $ids)
    {
        $this->createOrUpdateMultiple($this->repo->setIds($ids)->get());
    }

    public function createOrUpdateMultiple(Collection $partner_orders)
    {
        $partner_orders->each(function (PartnerOrder $partner_order) {
            try {
                $this->createOrUpdate($partner_order);
            } catch (\Exception $e) {
            };
        });
    }

    private function getReportData(PartnerOrder $partner_order)
    {
        $report_data = $this->presenter->setPartnerOrder($partner_order)->getForTable() + [
            'id' => $partner_order->id,
            'report_updated_at' => Carbon::now()
        ];
        return $report_data;
    }
}