<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Sheba\Business\Payslip\Excel as PaySlipExcel;
use App\Sheba\Business\Payslip\PayReport\BkashSalaryReportExcel;
use App\Sheba\Business\Payslip\PayReportList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Sheba\Business\Payslip\PayReport\PayReportDetails;
use Sheba\Dal\Payslip\PayslipRepository;

class PayReportController extends Controller
{
    /** @var PayslipRepository */
    private $payslipRepo;

    /**
     * PayReportController constructor.
     * @param PayslipRepository $payslip_repo
     */
    public function __construct(PayslipRepository $payslip_repo)
    {
        $this->payslipRepo = $payslip_repo;
    }

    /**
     * @param Request $request
     * @param PayReportList $pay_report_list
     * @param PaySlipExcel $pay_slip_excel
     * @return JsonResponse
     */
    public function index(Request $request, PayReportList $pay_report_list, PaySlipExcel $pay_slip_excel)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $payroll_setting = $business->payrollSetting;
        list($offset, $limit) = calculatePagination($request);
        $cache_key = 'pay_report_business_'.$business->id.'year_month_'.$request->month_year;
        $payslip = Cache::remember($cache_key, (60*60*24) , function () use ($business, $request, $pay_report_list) {
            $payslip['data'] =  $pay_report_list->setBusiness($business)
            ->setSearch($request->search)
            ->setSortKey($request->sort)
            ->setSortColumn($request->sort_column)
            ->setMonthYear($request->month_year)
            ->setDepartmentID($request->department_id)
            ->setGrossSalaryProrated($request->gross_salary_prorated)
            ->get();
            $payslip['total'] = $pay_report_list->getTotal();
            $payslip['is_prorated_filter_applicable'] = $pay_report_list->getIsProratedFilterApplicable();
            return $payslip;
        });
        $total_calculation = $payslip['total'];
        $is_prorated_filter_applicable = $payslip['is_prorated_filter_applicable'];
        $count = count($payslip['data']);
        if ($request->file == 'excel') return $pay_slip_excel->setPayslipData($payslip['data']->toArray())->setPayslipName('Pay_report')->get();
        if ($request->limit == 'all') $limit = $count;
        $payslip = collect($payslip['data'])->splice($offset, $limit);

        return api_response($request, null, 200, [
            'payslip' => $payslip,
            'total_calculation' => $total_calculation,
            'total' => $count,
            'is_prorated_filter_applicable' => $is_prorated_filter_applicable,
            'is_enable' => $payroll_setting->is_enable
        ]);
    }

    /**
     * @param $business
     * @param $payslip
     * @param Request $request
     * @param PayReportDetails $pay_report_details
     * @return JsonResponse
     */
    public function show($business, $payslip, Request $request, PayReportDetails $pay_report_details)
    {
        $pay_slip = $this->payslipRepo->find($payslip);
        if (!$pay_slip) return api_response($request, null, 404);
        $pay_report_detail = $pay_report_details->setPayslip($pay_slip)->setMonthYear($request->month_year)->get();

        if ($request->file == 'pdf') return App::make('dompdf.wrapper')->loadView('pdfs.payslip.payroll_details', compact('pay_report_detail'))->download("payroll_details.pdf");

        return api_response($request, null, 200, ['pay_report_detail' => $pay_report_detail]);
    }

    public function lastDisbursedMonth(Request $request, PayReportList $pay_report_list)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $last_disbursed_month = $pay_report_list->setBusiness($business)->getDisbursedMonth();

        return api_response($request, null, 200, ['last_disbursed_month' => $last_disbursed_month]);
    }

    /**
     * @param Request $request
     * @param PayReportList $pay_report_list
     * @return JsonResponse
     */
    public function bkashSalaryReport(Request $request, PayReportList $pay_report_list)
    {
        /** @var Business $business */
        $business = $request->business;

        $payslip = $pay_report_list->setBusiness($business)
            ->setMonthYear($request->month_year)
            ->getBkashSalaryData();

        $bkash_salary_report =  (new BkashSalaryReportExcel)->setEmployeeData($payslip->toArray())->download();

        return api_response($request, null, 200, ['bkash_salary_report' => $bkash_salary_report]);
    }
}
