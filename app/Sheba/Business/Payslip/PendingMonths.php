<?php namespace App\Sheba\Business\Payslip;

use App\Models\Business;
use Sheba\Dal\Payslip\PayslipRepository;
use Sheba\Dal\Payslip\Status;
use Sheba\Dal\TaxHistory\TaxHistoryRepository;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class PendingMonths
{
    /*** @var Business */
    private $business;
    /*** @var BusinessMemberRepositoryInterface */
    private $businessMemberRepository;
    /*** @var PayslipRepository */
    private $payslipRepositoryInterface;
    private $businessMemberIds;
    /*** @var TaxHistoryRepository $taxHistoryRepository*/
    private $taxHistoryRepository;


    /**
     * PendingMonths constructor.
     * @param BusinessMemberRepositoryInterface $business_member_repository
     * @param PayslipRepository $payslip_repository_interface
     * @param TaxHistoryRepository $tax_history_repository
     */
    public function __construct(BusinessMemberRepositoryInterface $business_member_repository,
                                PayslipRepository $payslip_repository_interface, TaxHistoryRepository $tax_history_repository)
    {
        $this->businessMemberRepository = $business_member_repository;
        $this->payslipRepositoryInterface = $payslip_repository_interface;
        $this->taxHistoryRepository = $tax_history_repository;
    }

    /**
     * @param Business $business
     * @return $this
     */
    public function setBusiness(Business $business)
    {
        $this->business = $business;
        $this->businessMemberIds = $this->business->getAccessibleBusinessMember()->pluck('id')->toArray();
        return $this;
    }

    /**
     * @return array
     */
    public function get()
    {

        $month_year = $this->payslipRepositoryInterface->builder()
            ->selectRaw('DATE_FORMAT(schedule_date, "%Y-%m") as formatted_date')
            ->where('status', Status::PENDING)
            ->whereIn('business_member_id', $this->businessMemberIds)
            ->orderBy('schedule_date', 'DESC')
            ->distinct()
            ->get()->toArray();
        return $this->getFormattedData(array_flatten($month_year));
    }

    public function getLastGeneratedTaxReport()
    {
        $tax_history = $this->taxHistoryRepository->getTaxReportByBusinessMemberIds($this->businessMemberIds)->orderBy('generated_at', 'DESC')->first();
        if (!$tax_history) return null;
        return $tax_history->generated_at;
    }


    /**
     * @param $values
     * @return array
     */
    private function getFormattedData($values)
    {
        $months_years = [];
        foreach ($values as $data) {
            $split_data = explode("-", $data);
            $monthName = date('F', mktime(0, 0, 0, $split_data[1], 10));
            array_push($months_years, [
                'value' => $data,
                'viewValue' => $monthName . ' ' . $split_data[0]
            ]);
        }
        return $months_years;
    }
}
