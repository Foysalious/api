<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Sheba\Business\BusinessBasicInformation;
use App\Sheba\Business\PayrollSetting\PayrollConstGetter;
use App\Sheba\Business\Payslip\TaxHistory\TaxHistoryExcel;
use App\Sheba\Business\Payslip\TaxHistoryList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use NumberFormatter;
use Sheba\Dal\PayrollComponent\Components;
use Sheba\Dal\PayrollComponent\PayrollComponentRepository;
use Sheba\Dal\PayrollComponent\Type;
use Sheba\Dal\PayrollSetting\PayrollSettingRepository;
use Sheba\Dal\Payslip\PayslipRepository;
use Sheba\Dal\TaxHistory\TaxHistoryRepository;
use Sheba\Helpers\TimeFrame;

class TaxHistoryController extends Controller
{
    use BusinessBasicInformation;

    /*** @var TaxHistoryRepository $taxHistoryRepo */
    private $taxHistoryRepo;
    /*** @var TimeFrame $timeFrame */
    private $timeFrame;
    /** @var PayrollSettingRepository $payrollSettingsRepo */
    private $payrollSettingsRepo;
    /**
     * @var PayrollComponentRepository
     */
    private $payrollComponentRepo;

    public function __construct(TimeFrame $time_frame, PayrollSettingRepository $payroll_settings_repo, TaxHistoryRepository $tax_history_repo, PayrollComponentRepository $payroll_component_repo)
    {
        $this->timeFrame = $time_frame;
        $this->payrollSettingsRepo = $payroll_settings_repo;
        $this->taxHistoryRepo = $tax_history_repo;
        $this->payrollComponentRepo = $payroll_component_repo;
    }

    public function index(Request $request, TaxHistoryList $tax_history_list, TaxHistoryExcel $tax_history_excel)
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
        if ($request->file == 'excel') return $tax_history_excel->setTaxHistoryData($tax_report->toArray())->get();
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

    public function downloadBusinessMemberTaxCertificate($business, $business_member_id, $id, Request $request, PayslipRepository $payslip_repository)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $tax_report_history = $this->taxHistoryRepo->find($id);
        if (!$tax_report_history) return api_response($request, null, 404);
        if ($tax_report_history->business_member_id !== intval($business_member_id)) return api_response($request, null, 404);
        $generated_at = $tax_report_history->generated_at;
        $period = $generated_at->format('F Y');
        $generated_at = $generated_at->format('Y-m-d');
        $payslip = $payslip_repository->where('business_member_id', $business_member_id)->whereDate('schedule_date', '=', $generated_at)->first();
        $business_name = $business->name;
        $business_logo = $this->isDefaultImageByUrl($business->logo) ? null : $business->logo;
        $employee = $payslip->businessMember;
        $employee_profile = $employee->profile();
        $employee_role = $employee->role;
        $salary_breakdown = json_decode($payslip->salary_breakdown, 1);
        $gross_salary_breakdown = $salary_breakdown['gross_salary_breakdown'];
        $total_gross_salary = $gross_salary_breakdown['gross_salary'];
        $gross_salary_breakdown = $this->getGrossSalaryBreakdown($gross_salary_breakdown, $business->payrollSetting);
        $gross_amount_in_word = $this->getAmountInWord($total_gross_salary);
        $net_payable = $this->getNetPayable($salary_breakdown);
        $yearly_tax = $tax_report_history->yearly_amount;
        $yearly_tax_in_word = $this->getAmountInWord($yearly_tax);
        return App::make('dompdf.wrapper')
            ->loadView('pdfs.payroll.tax_certificate', compact('business_name', 'business_logo', 'employee_profile', 'employee_role', 'gross_salary_breakdown', 'total_gross_salary', 'gross_amount_in_word', 'net_payable', 'period', 'yearly_tax', 'yearly_tax_in_word'))
            ->download("tax_certificate.pdf");
    }

    private function getGrossSalaryBreakdown($gross_salary_breakdown, $payroll_setting)
    {
        $data = [];
        $x = 0;
        foreach ($gross_salary_breakdown as $component_name => $component_value) {
            if ($component_name == 'gross_salary') continue;
            else if ($component_name == PayrollConstGetter::MEDICAL_ALLOWANCE){
                $data[$x]['value'] = Components::getComponents($component_name)['value'];
                $data[$x]['display_index'] = 3;
                $data[$x]['amount'] = $component_value;
            }
            else if ($component_name == PayrollConstGetter::BASIC_SALARY) {
                $data[$x]['value'] = Components::getComponents($component_name)['value'];
                $data[$x]['display_index'] = 1;
                $data[$x]['amount'] = $component_value;

            }
            else if ($component_name == PayrollConstGetter::CONVEYANCE) {
                $data[$x]['value'] = Components::getComponents($component_name)['value'];
                $data[$x]['display_index'] = 4;
                $data[$x]['amount'] = $component_value;

            }
            else if ($component_name == PayrollConstGetter::HOUSE_RENT) {

                $data[$x]['value'] = Components::getComponents($component_name)['value'];
                $data[$x]['display_index'] = 2;
                $data[$x]['amount'] = $component_value;
            }
            else {
                $custom_component = $this->payrollComponentRepo->where('name', $component_name)->where('payroll_setting_id', $payroll_setting->id)->first();
                $name = $custom_component->value;
                $data[$x]['value'] = $name;
                $data[$x]['display_index'] = 9;
                $data[$x]['amount'] = $component_value;

            }
            $x++;
        }
        usort($data, array($this,'componentSortByDisplayIndex'));
        return $data;
    }

    private function componentSortByDisplayIndex($a, $b)
    {
        if ($a['display_index'] < $b['display_index']) return 0;
        return 1;
    }

    private function getAmountInWord($amount)
    {
        return ucwords(str_replace('-', ' ', (new NumberFormatter("en", NumberFormatter::SPELLOUT))->format($amount)));
    }

    private function getNetPayable($salary_breakdown)
    {
        $net_payable = 0;
        foreach ($salary_breakdown['payroll_component'] as $type => $breakdown) {
            foreach ($breakdown as $amount) {
                if ($type === Type::ADDITION) {
                    $net_payable += $amount;
                }
                if ($type === Type::DEDUCTION) {
                    $net_payable -= $amount;
                }
            }
        }
        return ($salary_breakdown['gross_salary_breakdown']['gross_salary'] + $net_payable);
    }

}