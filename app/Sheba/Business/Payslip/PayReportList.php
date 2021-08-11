<?php namespace App\Sheba\Business\Payslip;

use App\Models\Business;
use App\Transformers\Business\PayReportListTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\Dal\PayrollComponent\Components;
use Sheba\Dal\Payslip\PayslipRepository;
use Sheba\Dal\Payslip\Status;
use Sheba\Dal\Salary\SalaryRepository;

class PayReportList
{
    private $business;
    private $payslipRepository;
    private $payslipList;
    private $salaryRepository;
    private $search;
    private $sortColumn;
    private $sort;
    private $businessMemberIds;
    private $monthYear;
    private $departmentID;
    private $payslip;
    private $isProratedFilterApplicable;

    /**
     * PayReportList constructor.
     * @param PayslipRepository $payslip_repository
     * @param SalaryRepository $salary_repository
     */
    public function __construct(PayslipRepository $payslip_repository, SalaryRepository $salary_repository)
    {
        $this->payslipRepository = $payslip_repository;
        $this->salaryRepository = $salary_repository;
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

    /**
     * @return \Illuminate\Support\Collection
     */
    public function get()
    {
        $this->runPayslipQuery();
        $this->payslip = $this->getData();
        return $this->payslip;
    }

    public function getDisbursedMonth()
    {
        $payslip = $this->payslipRepository->getPaySlipByStatus($this->businessMemberIds, Status::DISBURSED)->select('schedule_date')->orderBy('schedule_date', 'DESC')->first();
        if (!$payslip) return null;
        return $payslip->schedule_date->format('Y-m');
    }

    private function runPayslipQuery()
    {
        $payslips = $this->payslipRepository->getPaySlipByStatus($this->businessMemberIds, Status::DISBURSED)->orderBy('id', 'DESC');
        if ($this->monthYear) $payslips = $this->filterByMonthYear($payslips);
        if ($this->departmentID) $payslips = $this->filterByDepartment($payslips);
        $this->payslipList = $payslips->get();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    private function getData()
    {
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $payslip_transformer = new PayReportListTransformer();
        $payslip_list = new Collection($this->payslipList, $payslip_transformer);
        $payslip_list = collect($manager->createData($payslip_list)->toArray()['data']);

        if ($this->search) $payslip_list = collect($this->searchWithEmployeeName($payslip_list))->values();
        if ($this->sort && $this->sortColumn) $payslip_list = $this->sortByColumn($payslip_list, $this->sortColumn, $this->sort)->values();
        $this->isProratedFilterApplicable = $payslip_transformer->getIsProratedFilterApplicable();
        return $payslip_list;
    }

    /**
     * @return array
     */
    public function getTotal()
    {
        return [
            'gross_salary' => $this->payslip->sum('gross_salary'),
            'addition' => $this->payslip->sum('addition'),
            'deduction' => $this->payslip->sum('deduction'),
            'net_payable' => $this->payslip->sum('net_payable'),
        ];
    }

    public function getIsProratedFilterApplicable()
    {
        return $this->isProratedFilterApplicable;
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

    /**
     * @param $data
     * @param $column
     * @param string $sort
     * @return mixed
     */
    private function sortByColumn($data, $column, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($data)->$sort_by(function ($value, $key) use ($column) {
            return strtoupper($value[$column]);
        });
    }

    /**
     * @param $payslips
     * @return mixed
     */
    private function filterByMonthYear($payslips)
    {
        return $payslips->where('schedule_date', 'LIKE', '%' . $this->monthYear . '%');
    }

    /**
     * @param $payslips
     * @return mixed
     */
    private function filterByDepartment($payslips)
    {
        return $payslips->whereHas('businessMember', function ($q) {
            $q->whereHas('role', function ($q) {
                $q->whereHas('businessDepartment', function ($q) {
                    $q->where('business_departments.id', $this->departmentID);
                });
            });
        });
    }
}
