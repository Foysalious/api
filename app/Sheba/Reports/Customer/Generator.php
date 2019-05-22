<?php namespace Sheba\Reports\Customer;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Sheba\Dal\CustomerReport\CustomerReport;

class Generator
{
    private $presenter;
    private $query;

    public function __construct(Presenter $presenter, Query $query)
    {
        $this->presenter = $presenter;
        $this->query = $query;
        $this->presenter->setIsAdvanced(true);
        $this->query->setIsAdvanced(true);
    }

    public function createOrUpdate(Customer $customer)
    {
        if ($report = CustomerReport::find($customer->id)) {
            $report->update($this->getReportData($customer));
        } else {
            $this->create($customer);
        }
    }

    public function create(Customer $customer)
    {
        return CustomerReport::create($this->getReportData($customer));
    }

    public function refresh($skip = 0)
    {
        $limit = 500;
        $batch = (int)ceil(Customer::count() / $limit);

        $i = 0;
        if ($skip) {
            $this->createOrUpdateMultipleByLimitOffset($limit - $skip % $limit, $skip);
            $i = (int)ceil($skip / $limit);
        } else {
            CustomerReport::truncate();
        }

        for (; $i < $batch; $i++) {
            $this->createOrUpdateMultipleByLimitOffset($limit, $limit * $i);
        }
    }

    public function createOrUpdateMultipleByLimitOffset($limit, $offset)
    {
        $this->createOrUpdateMultiple($this->query->build()->skip($offset)->take($limit)->get());
    }

    public function createOrUpdateMultipleById(array $ids)
    {
        $this->createOrUpdateMultiple($this->query->build()->whereIn('id', $ids)->get());
    }

    public function createOrUpdateMultiple(Collection $customers)
    {
        $customers->each(function (Customer $customer) {
            try {
                $this->createOrUpdate($customer);
            } catch (\Exception $e) {
                dd($e->getMessage());
            };
        });
    }

    private function getReportData(Customer $customer)
    {
        $report_data = $this->presenter->setCustomer($customer)->getForTable() + [
            'report_updated_at' => Carbon::now()
        ];
        $report_data['name'] = "test";
        return $report_data;
    }
}