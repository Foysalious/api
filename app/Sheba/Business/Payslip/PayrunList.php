<?php namespace App\Sheba\Business\Payslip;


use App\Models\Business;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Dal\Payslip\PayslipRepository;
use Sheba\Dal\Salary\SalaryRepository;
use Sheba\Dal\Payslip\Status;

class PayrunList
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
            ->where('status', Status::PENDING)
            ->whereIn('business_member_id', $business_member_ids)->with(['businessMember' => function ($q){
                    $q->with(['role' => function ($q) {
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
               'business_member_id' => $playslip->business_member_id,
               'department' => $playslip->businessMember->department()->name,
               'gross_salary' => floatval($gross_salary),
               'net_payable' => floatval($gross_salary)
            ]);
        }
        return $data;
    }

    private function getGrossSalary($business_member_id)
    {
        return $this->SalaryRepository->where('business_member_id', $business_member_id)->pluck('gross_salary', 'business_member_id')->first();
    }
}
