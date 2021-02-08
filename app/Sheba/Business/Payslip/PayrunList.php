<?php namespace App\Sheba\Business\Payslip;


use App\Models\Business;
use App\Transformers\Business\PayRunListTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\ArraySerializer;
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

    public function get()
    {
        $this->runPayslipQuery();

        return $this->getData();
    }

    private function runPayslipQuery()
    {
        $payslip = $this->payslipRepositoryInterface->getPaySlipByStatus($this->businessMemberIds, Status::PENDING);
        $this->payslipList = $payslip->get();
    }

    private function getData()
    {
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $payslip_list = new Collection($this->payslipList, new PayRunListTransformer());
        $payslip_list = collect($manager->createData($payslip_list)->toArray()['data']);

        return $payslip_list;
    }
}
