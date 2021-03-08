<?php namespace App\Sheba\Business\Payslip;


use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\BusinessRole;
use App\Transformers\Business\PayRunListTransformer;
use Carbon\Carbon;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\Dal\PayrollComponent\Components;
use Sheba\Dal\PayrollComponent\Type;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Dal\Payslip\PayslipRepository;
use Sheba\Dal\Salary\SalaryRepository;
use Sheba\Dal\Payslip\Status;

class PayrunList
{

    /*** @var Business */
    private $business;
    /*** @var BusinessMemberRepositoryInterface */
    private $businessMemberRepository;
    /*** @var PayslipRepository */
    private $payslipRepositoryInterface;
    /** @var SalaryRepository */
    private $salaryRepository;
    private $businessMemberIds;
    private $payslipList;
    private $search;
    private $sortColumn;
    private $sort;
    private $monthYear;
    private $departmentID;
    /**
     * @var \Illuminate\Support\Collection
     */
    private $payslip;

    /**
     * PayrunList constructor.
     * @param BusinessMemberRepositoryInterface $business_member_repository
     * @param PayslipRepository $payslip_repository_interface
     * @param SalaryRepository $salary_repository
     */
    public function __construct(BusinessMemberRepositoryInterface $business_member_repository, PayslipRepository $payslip_repository_interface, SalaryRepository $salary_repository)
    {
        $this->businessMemberRepository = $business_member_repository;
        $this->payslipRepositoryInterface = $payslip_repository_interface;
        $this->salaryRepository = $salary_repository;
    }

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        $this->businessMemberIds = $this->business->getAccessibleBusinessMember()->pluck('id')->toArray();
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

    public function get()
    {
        $this->runPayslipQuery();
        $this->payslip = $this->getData();
        return $this->payslip;
    }

    private function runPayslipQuery()
    {
        $payslips = $this->payslipRepositoryInterface->getPaySlipByStatus($this->businessMemberIds, Status::PENDING)->orderBy('id', 'DESC');
        if ($this->monthYear) $payslips = $this->filterByMonthYear($payslips);
        if ($this->departmentID) $payslips = $this->filterByDepartment($payslips);
        $this->payslipList = $payslips->get();
    }

    private function getData()
    {
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $payslip_list = new Collection($this->payslipList, new PayRunListTransformer());
        $payslip_list = collect($manager->createData($payslip_list)->toArray()['data']);

        if ($this->search) $payslip_list = collect($this->searchWithEmployeeName($payslip_list))->values();
        if ($this->sort && $this->sortColumn) $payslip_list = $this->sortByColumn($payslip_list, $this->sortColumn, $this->sort)->values();

        return $payslip_list;
    }

    public function getTotal()
    {
        return [
            'gross_salary' => $this->payslip->sum('gross_salary'),
            'addition' => $this->payslip->sum('addition'),
            'deduction' => $this->payslip->sum('deduction'),
            'net_payable' => $this->payslip->sum('net_payable'),
        ];
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

    public function getComponents($payroll_components)
    {
        $final_data = [];
        foreach ($payroll_components as $key => $payroll_component) {
            array_push($final_data, [
                'key' => $payroll_component->name,
                'title' => $payroll_component->is_default ? Components::getComponents($payroll_component->name)['value'] : ucwords(implode(" ", explode("_",$payroll_component->name))),
                'type' => $payroll_component->type
            ]);
        }
        return $final_data;
    }
}
