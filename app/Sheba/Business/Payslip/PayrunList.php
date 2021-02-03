<?php namespace App\Sheba\Business\Payslip;


use App\Models\Business;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Dal\Payslip\PayslipRepository;
use Sheba\Dal\Salary\SalaryRepository;

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
            ->whereIn('business_member_id', $business_member_ids);
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
               'gross_salary' => $gross_salary[0],
               'net_payable' => $gross_salary[0]
            ]);
        }
        return $data;
    }

    private function getGrossSalary($business_member_id)
    {
        return $this->SalaryRepository->where('business_member_id', $business_member_id)->pluck('gross_salary');
    }
}
