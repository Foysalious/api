<?php namespace Sheba\Reports\Affiliate;

use Carbon\Carbon;
use App\Models\Affiliate;
use Sheba\Dal\AffiliateReport\AffiliateReport;

class Generator
{
    private $presenter;
    private $query;

    public function __construct(Presenter $presenter, Query $query)
    {
        $this->presenter = $presenter;
        $this->query = $query;
    }

    public function createOrUpdate(Affiliate $affiliate)
    {
        if ($report = AffiliateReport::find($affiliate->id)) {
            $report->update($this->getReportData($affiliate));
        } else {
            $this->create($affiliate);
        }
    }

    public function create(Affiliate $affiliate)
    {
        return AffiliateReport::create($this->getReportData($affiliate));
    }

    public function refresh($skip = 0)
    {
        $limit = 500;
        $batch = ceil(Affiliate::count() / $limit);

        $i = 0;
        if ($skip) {
            $this->createMultiple($limit - $skip % $limit, $skip);
            $i = ceil($skip / $limit);
        } else {
            AffiliateReport::truncate();
        }

        for (; $i < $batch; $i++) {
            $this->createMultiple($limit, $limit * $i);
        }
    }

    private function createMultiple($limit, $offset)
    {
        $affiliates = $this->query->build()->skip($offset)->take($limit)->get();
        $affiliates->each(function (Affiliate $affiliate) {
            try {
                $this->create($affiliate);
            } catch (\Exception $e) { };
        });
    }

    private function getReportData(Affiliate $affiliate)
    {
        $report_data = $this->presenter->setAffiliate($affiliate)->get() + [
            'report_updated_at' => Carbon::now()
        ];
        return $report_data;
    }
}