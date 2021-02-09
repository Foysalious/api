<?php namespace App\Sheba\Business\Payslip;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\BusinessRole;
use App\Transformers\Business\PayReportListTransformer;
use Carbon\Carbon;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\Dal\Payslip\PayslipRepository;
use Sheba\Dal\Payslip\Status;
use Sheba\Dal\Salary\SalaryRepository;

class PayReportList
{
    /*** @var Business */
    private $business;
    /*** @var PayslipRepository */
    private $payslipRepositoryInterface;
    private $payslipList;
    /**
     * @var SalaryRepository
     */
    private $SalaryRepository;
    private $search;
    private $sortColumn;
    private $sort;
    private $businessMemberIds;
    private $monthYear;
    private $departmentID;

    /**
     * PayReportList constructor.
     * @param PayslipRepository $payslip_repository_interface
     * @param SalaryRepository $salary_repository
     */
    public function __construct(PayslipRepository $payslip_repository_interface, SalaryRepository $salary_repository)
    {
        $this->payslipRepositoryInterface = $payslip_repository_interface;
        $this->SalaryRepository = $salary_repository;
    }

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        $this->businessMemberIds = $this->business->getAccessibleBusinessMember()->pluck('id')->toArray();
        return $this;
    }

    /**
     * @param $search
     * @return $this
     */
    public function setSearch($search)
    {
        $this->search = $search;
        return $this;
    }

    /**
     * @param $sort
     * @return $this
     */
    public function setSortKey($sort)
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @param $column
     * @return $this
     */
    public function setSortColumn($column)
    {
        $this->sortColumn = $column;
        return $this;
    }

    /**
     * @param $month_year
     * @return $this
     */
    public function setMonthYear($month_year)
    {
        $this->monthYear = $month_year;
        return $this;
    }

    /**
     * @param $department_id
     * @return $this
     */
    public function setDepartmentID($department_id)
    {
        $this->departmentID = $department_id;
        return $this;
    }

    public function get()
    {
        $this->runPayslipQuery();

        return $this->getData();
    }

    private function runPayslipQuery()
    {
        $payslip = $this->payslipRepositoryInterface->getPaySlipByStatus($this->businessMemberIds, Status::DISBURSED);
        $this->payslipList = $payslip->get();

        if ($this->monthYear) {
            $this->payslipList = $this->filterByMonthYear($this->monthYear, $this->payslipList);
        }

        if ($this->departmentID) {
            $this->payslipList = $this->filterByDepartment($this->departmentID, $this->payslipList);
        }
    }

    private function getData()
    {
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $payslip_list = new Collection($this->payslipList, new PayReportListTransformer());
        $payslip_list = collect($manager->createData($payslip_list)->toArray()['data']);

        if ($this->search)
            $payslip_list = collect($this->searchWithEmployeeName($payslip_list))->values();

        if ($this->sort && $this->sortColumn) {
            $payslip_list = $this->sortByColumn($payslip_list, $this->sortColumn, $this->sort)->values();
        }

        return $payslip_list;
    }

    /**
     * @param $data
     * @return array
     */
    private function searchWithEmployeeName($data)
    {
        return array_where($data, function ($key, $value) {
            return str_contains(strtoupper($value['employee_name']), strtoupper($this->search));
        });
    }

    private function sortByColumn($data, $column, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($data)->$sort_by(function ($value, $key) use ($column){
            return strtoupper($value[$column]);
        });
    }

    private function filterByMonthYear($month_year, $data)
    {
        $split_data = explode("-", $month_year);
        $first_date = Carbon::create($split_data[1], $split_data[0])->startOfMonth();
        $last_date = Carbon::create($split_data[1], $split_data[0])->lastOfMonth()->endOfDay();

        return $data->filter(function ($payslip) use ($first_date, $last_date) {
            $schedule_date = Carbon::parse($payslip->schedule_date);
            return $schedule_date->gte($first_date) && $schedule_date->lte($last_date);
        });
    }

    private function filterByDepartment($department_id, $data)
    {
        return $data->filter(function ($payslip) use ($department_id) {
            /** @var BusinessMember $business_member */
            $business_member = $payslip->businessMember;
            /** @var BusinessRole $role */
            $role = $business_member->role;
            if ($role) return $role->businessDepartment->id == $department_id;
        });
    }

}
