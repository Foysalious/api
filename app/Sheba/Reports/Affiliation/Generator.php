<?php namespace Sheba\Reports\Affiliation;

use App\Models\Affiliation;
use App\Models\AffiliationReport;
use Carbon\Carbon;

class Generator
{
    private $presenter;
    private $query;

    public function __construct(Presenter $presenter, Query $query)
    {
        $this->presenter = $presenter;
        $this->query = $query;
    }

    public function createOrUpdate(Affiliation $affiliation)
    {
        if ($report = AffiliationReport::find($affiliation->id)) {
            $report->update($this->getReportData($affiliation));
        } else {
            $this->create($affiliation);
        }
    }

    public function create(Affiliation $affiliation)
    {
        return AffiliationReport::create($this->getReportData($affiliation));
    }

    public function refresh($skip = 0)
    {
        $limit = 500;
        $batch = ceil(Affiliation::count() / $limit);

        $i = 0;
        if ($skip) {
            $this->createMultiple($limit - $skip % $limit, $skip);
            $i = ceil($skip / $limit);
        } else {
            AffiliationReport::truncate();
        }

        for (; $i < $batch; $i++) {
            $this->createMultiple($limit, $limit * $i);
        }
    }

    private function createMultiple($limit, $offset)
    {
        $affiliations = $this->query->build()->skip($offset)->take($limit)->get();
        $affiliations->each(function (Affiliation $affiliation) {
            try {
                $this->create($affiliation);
            } catch (\Exception $e) { };
        });
    }

    private function getReportData(Affiliation $affiliation)
    {
        $report_data = $this->presenter->setAffiliation($affiliation)->getForTable() + [
                'id' => $affiliation->id,
                'report_updated_at' => Carbon::now()
            ];

        return $report_data;
    }
}