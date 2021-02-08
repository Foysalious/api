<?php namespace App\Sheba\Business\Payslip;

use App\Models\Business;
use Sheba\Dal\Payslip\PayslipRepository;
use Sheba\Dal\Payslip\Status;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class PendingMonths
{
    /*** @var Business */
    private $business;
    /*** @var BusinessMemberRepositoryInterface */
    private $businessMemberRepository;
    /*** @var PayslipRepository */
    private $PayslipRepositoryInterface;


    /**
     * PendingMonths constructor.
     * @param BusinessMemberRepositoryInterface $business_member_repository
     * @param PayslipRepository $payslip_repository_interface
     */
    public function __construct(BusinessMemberRepositoryInterface $business_member_repository,
                                PayslipRepository $payslip_repository_interface)
    {
        $this->businessMemberRepository = $business_member_repository;
        $this->PayslipRepositoryInterface = $payslip_repository_interface;
    }

    /**
     * @param Business $business
     * @return $this
     */
    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    /**
     * @return array
     */
    public function get()
    {
        $business_member_ids = $this->getBusinessMemberIds();
        $month_year = $this->PayslipRepositoryInterface->builder()
            ->selectRaw('DATE_FORMAT(schedule_date, "%m-%Y") as formatted_date')
            ->where('status', Status::PENDING)
            ->whereIn('business_member_id', $business_member_ids)
            ->distinct()
            ->get()->toArray();
        return $this->getFormattedData(array_flatten($month_year));
    }

    /**
     * @return mixed
     */
    private function getBusinessMemberIds()
    {
        return $this->businessMemberRepository->where('business_id', $this->business->id)->pluck('id')->toArray();
    }

    /**
     * @param $datas
     * @return array
     */
    private function getFormattedData($datas) {
        $months_years = [];
        foreach ($datas as $data) {
            $split_data = explode("-", $data);
            $monthName = date('F', mktime(0, 0, 0, $split_data[0], 10));
            array_push($months_years,[
                'value' => $data,
                'viewValue' => $monthName.' '.$split_data[1]
            ]);
        }
        return $months_years;
    }
}