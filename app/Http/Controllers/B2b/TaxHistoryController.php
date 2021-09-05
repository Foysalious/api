<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Sheba\Business\Payslip\TaxHistoryList;
use Sheba\Dal\PayrollSetting\PayrollSettingRepository;
use Sheba\Dal\TaxHistory\TaxHistoryRepository;
use Sheba\Helpers\TimeFrame;

class TaxHistoryController extends Controller
{
    /*** @var TaxHistoryRepository $taxHistoryRepo*/
    private $taxHistoryRepo;
    /*** @var TimeFrame $timeFrame*/
    private $timeFrame;
    /** @var PayrollSettingRepository $payrollSettingsRepo*/
    private $payrollSettingsRepo;

    public function __construct(TimeFrame $time_frame, PayrollSettingRepository $payroll_settings_repo, TaxHistoryRepository $tax_history_repo)
    {
        $this->timeFrame = $time_frame;
        $this->payrollSettingsRepo = $payroll_settings_repo;
        $this->taxHistoryRepo = $tax_history_repo;
    }

    public function index(Request $request, TaxHistoryList $tax_history_list)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        list($offset, $limit) = calculatePagination($request);
        $time_period = $this->timeFrame->forAMonth($request->month, $request->year);
        $tax_report = $tax_history_list->setBusiness($business)->setTimePeriod($time_period)
                                       ->setDepartmentID($request->department_id)->setSearch($request->search)
                                       ->setSortKey($request->sort)->setSortColumn($request->sort_column)
                                       ->get();
        $total_report_count = $tax_report->count();
        $total_tax_amount = $tax_report->sum('total_tax_amount_monthly');
        
        $tax_report = collect($tax_report)->splice($offset, $limit);
        return api_response($request, null, 200, ['tax_history' => $tax_report, 'total_tax_amount' => $total_tax_amount, 'show_download_report_banner' => $business->payrollSetting->show_tax_report_download_banner, 'total' => $total_report_count]);
    }

    public function updateReportShowBanner(Request $request)
    {
        $this->validate($request, [
            'show_banner' => 'required'
        ]);
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $payroll_settings = $this->payrollSettingsRepo->find($business->payrollSetting->id);
        if (!$payroll_settings) return api_response($request, null, 401);
        $this->payrollSettingsRepo->update($payroll_settings, ['show_tax_report_download_banner' => $request->show_banner]);
        return api_response($request, null, 200);
    }

    public function downloadBusinessMemberTaxCertificate($business_member_id, $id, Request $request)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $tax_report_history = $this->taxHistoryRepo->find($id);
        if (!$tax_report_history) return api_response($request, null, 404);
        if ($tax_report_history->business_member_id !== $business_member_id) return api_response($request, null, 404);
    }

}