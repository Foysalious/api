<?php namespace Sheba\Reports\Complain;

use Sheba\Dal\Complain\Model as Complain;
use App\Models\ComplainReport;
use Carbon\Carbon;
use Sheba\Reports\Complain\Getters\RawDataGetter;

class Generator
{
    private $presenter;
    private $rawDataGetter;

    public function __construct(Presenter $presenter, RawDataGetter $raw_data_getter)
    {
        $this->presenter = $presenter;
        $this->rawDataGetter = $raw_data_getter;
    }

    public function createOrUpdate(Complain $complain)
    {
        if ($report = ComplainReport::find($complain->id)) {
            $report->update($this->getReportData($complain));
        } else {
            $this->create($complain);
        }
    }

    public function create(Complain $complain)
    {
        return ComplainReport::create($this->getReportData($complain));
    }

    public function refresh($skip = 0)
    {
        $limit = 500;
        $batch = ceil(Complain::count() / $limit);

        $i = 0;
        if ($skip) {
            $this->createMultiple($limit - $skip % $limit, $skip);
            $i = ceil($skip / $limit);
        } else {
            ComplainReport::truncate();
        }

        for (; $i < $batch; $i++) {
            $this->createMultiple($limit, $limit * $i);
        }
    }

    private function createMultiple($limit, $offset)
    {
        $complains = $this->rawDataGetter->getQuery()->skip($offset)->take($limit)->get();
        $complains = $this->rawDataGetter->mapCustomerFirstOrder($complains);
        $complains->each(function (Complain $complain) {
            try {
                $this->create($complain);
            } catch (\Exception $e) {
            };
        });
    }

    private function getReportData(Complain $complain)
    {
        $report_data = $this->presenter->setComplain($complain)->getForTable() + [
            'id' => $complain->id,
            'report_updated_at' => Carbon::now()
        ];
        return $report_data;
    }
}