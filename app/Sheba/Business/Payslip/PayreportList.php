<?php namespace App\Sheba\Business\Payslip;


use App\Models\Business;
use Sheba\Dal\Payslip\PayslipRepository;
use Sheba\Dal\Payslip\Status;
use Sheba\Dal\Salary\SalaryRepository;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class PayreportList
{
    /*** @var Business */
    private $business;
    private $businessMemberId;
    /*** @var BusinessMemberRepositoryInterface */
    private $businessMemberRepository;
    /*** @var PayslipRepository */
    private $PayslipRepositoryInterface;
    private $playslipList;
    /**
     * @var SalaryRepository
     */
    private $SalaryRepository;
    private $search;
    private $sortColumn;
    private $sort;

    /**
     * PayrunList constructor.
     * @param BusinessMemberRepositoryInterface $business_member_repository
     * @param PayslipRepository $payslip_repository_interface
     * @param SalaryRepository $slary_repository
     */
    public function __construct(BusinessMemberRepositoryInterface $business_member_repository, PayslipRepository $payslip_repository_interface, SalaryRepository $slary_repository)
    {
        $this->businessMemberRepository = $business_member_repository;
        $this->PayslipRepositoryInterface = $payslip_repository_interface;
        $this->SalaryRepository = $slary_repository;
    }

    public function setBusiness(Business $business)
    {
        $this->business = $business;
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

        return $this->getData();
    }

    private function runPayslipQuery()
    {
        $business_member_ids = [];
        $business_member_ids = $this->getBusinessMemberIds();
        $payslip = $this->PayslipRepositoryInterface->builder()
            ->select('id', 'business_member_id', 'schedule_date', 'status', 'salary_breakdown', 'created_at')
            ->where('status', Status::DISBURSED)
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

        $this->playslipList = $payslip->get();
    }

    private function getBusinessMemberIds()
    {
        return $this->businessMemberRepository->where('business_id', $this->business->id)->pluck('id')->toArray();
    }

    private function getData()
    {
        $data = [];
        foreach ($this->playslipList as $playslip) {
            $gross_salary = $this->getGrossSalary($playslip->business_member_id);
            array_push($data,[
                'id' =>  $playslip->id,
                'employee_id' => $playslip->businessMember->employee_id,
                'employee_name' => $playslip->businessMember->member->profile->name,
                'business_member_id' => $playslip->business_member_id,
                'department' => $playslip->businessMember->department()->name,
                'gross_salary' => floatval($gross_salary),
                'net_payable' => floatval($gross_salary)
            ]);
        }
        if ($this->search)
            $data = collect($this->searchWithEmployeeName($data))->values();

        if ($this->sort && $this->sortColumn) {
            $data = $this->sortByColumn($data, $this->sortColumn, $this->sort)->values();
        }

        return $data;
    }

    private function getGrossSalary($business_member_id)
    {
        return $this->SalaryRepository->where('business_member_id', $business_member_id)->pluck('gross_salary', 'business_member_id')->first();
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

}
