<?php namespace Sheba\Business\Payslip\PayReport;

use App\Sheba\Business\Payslip\PayReportList;
use App\Transformers\Business\ApprovalRequestTransformer;
use App\Transformers\Business\PayReportDetailsTransformer;
use App\Transformers\CustomSerializer;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Dal\Payslip\PayslipRepository;
use Sheba\Dal\Salary\SalaryRepository;

class PayReportDetails
{
    private $payslipRepository;
    private $salaryRepository;
    private $payslip;
    private $businessMember;
    private $business;
    private $monthYear;

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

    public function setPayslip($payslip)
    {
        $this->payslip = $payslip;
        $this->businessMember = $this->payslip->businessMember;
        $this->business = $this->businessMember->business;
        return $this;
    }

    /**
     * @param $month_year
     * @return $this
     */
    public function setMonthYear($month_year)
    {
        $this->monthYear = $month_year;
        if ($this->monthYear) $this->payslip = $this->payslipRepository->where('business_member_id', $this->businessMember->id)->where('schedule_date', 'LIKE', '%' . $this->monthYear . '%')->first();
        return $this;
    }

    /**
     * @return mixed
     */
    public function get()
    {
        if (!$this->payslip) return [];
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($this->payslip, new PayReportDetailsTransformer($this->businessMember));
        $payslip = $manager->createData($resource)->toArray()['data'];
        return $payslip;
    }
}