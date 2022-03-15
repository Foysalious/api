<?php namespace Sheba\Business\Payslip\PayReport;

use App\Transformers\Business\PayReportDetailsTransformer;
use App\Transformers\CustomSerializer;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Dal\Payslip\PayslipRepository;

class PayReportDetails
{
    private $payslipRepository;
    private $payslip;
    private $businessMember;

    /**
     * PayReportList constructor.
     * @param PayslipRepository $payslip_repository
     */
    public function __construct(PayslipRepository $payslip_repository)
    {
        $this->payslipRepository = $payslip_repository;
    }

    public function setPayslip($payslip)
    {
        $this->payslip = $payslip;
        $this->businessMember = $this->payslip->businessMember;
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
