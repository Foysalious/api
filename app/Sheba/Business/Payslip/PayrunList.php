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
use Sheba\Dal\Payslip\PayslipRepoImplementation;
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
    private $payslip;
    private $isProratedFilterApplicable;
    private $grossSalaryProrated;
    private $paysliprepo;

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
        $this->paysliprepo = app(PayslipRepoImplementation::class);//Test
    }

    /**
     * @param Business $business
     * @return $this
     */
    public function setBusiness(Business $business)
    {
        $this->business = $business;
        $this->businessMemberIds = $this->business->getActiveBusinessMember()->pluck('id')->toArray();
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

    public function setGrossSalaryProrated($gross_salary_prorated)
    {
        $this->grossSalaryProrated = $gross_salary_prorated;
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
        //$payslips = $this->payslipRepositoryInterface->getPaySlipByStatus($this->businessMemberIds, Status::PENDING)->orderBy('id', 'DESC');
        $payslips = $this->getPaySlipByStatus($this->businessMemberIds, Status::PENDING)->orderBy('id', 'DESC');
        if ($this->monthYear) $payslips = $this->filterByMonthYear($payslips);
        if ($this->departmentID) $payslips = $this->filterByDepartment($payslips);
        if($this->grossSalaryProrated) $this->filterByGrossSalaryProrated($payslips);
        $this->payslipList = $payslips->get();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    private function getData()
    {
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $payrun_list_transformer = new PayRunListTransformer();
        $payslip_list = new Collection($this->payslipList, $payrun_list_transformer);
        $payslip_list = collect($manager->createData($payslip_list)->toArray()['data']);

        if ($this->search) $payslip_list = collect($this->searchWithEmployeeName($payslip_list))->values();
        if ($this->sort && $this->sortColumn) $payslip_list = $this->sortByColumn($payslip_list, $this->sortColumn, $this->sort)->values();
        $this->isProratedFilterApplicable = $payrun_list_transformer->getIsProratedFilterApplicable();

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
     * @param $payroll_components
     * @return array
     */
    public function getComponents($payroll_components)
    {
        $final_data = [];
        foreach ($payroll_components as $payroll_component) {
            array_push($final_data, [
                'key' => $payroll_component->name,
                'title' => $payroll_component->is_default ? Components::getComponents($payroll_component->name)['value'] : ucwords(implode(" ", explode("_", $payroll_component->name))),
                'type' => $payroll_component->type
            ]);
        }
        return $final_data;
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

    private function filterByGrossSalaryProrated($payslips)
    {
        if ($this->grossSalaryProrated === 'yes') $payslips->where('joining_log', '<>', null);
        if ($this->grossSalaryProrated === 'no') $payslips->where('joining_log', null);
    }

    public function getPaySlipByStatus($business_member_ids, $status)
    {
        return $this->paysliprepo->where('status', $status)
            ->whereIn('business_member_id', $business_member_ids)->with(['businessMember' => function ($q){
                $q->with(['member' => function ($q) {
                    $q->select('id', 'profile_id')
                        ->with([
                            'profile' => function ($q) {
                                $q->select('id', 'name');
                            }]);
                },'role' => function ($q) {
                    $q->select('business_roles.id', 'business_department_id', 'name')->with([
                        'businessDepartment' => function ($q) {
                            $q->select('business_departments.id', 'business_id', 'name');
                        }
                    ]);
                }]);
            }]);
    }
}
