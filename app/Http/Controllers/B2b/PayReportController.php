<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessMember;
use Sheba\Business\Payslip\PayslipExcel;
use Carbon\Carbon;
use Sheba\Business\Payslip\PayReport\BkashSalaryReportExcel;
use App\Sheba\Business\Payslip\PayReportList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Facades\Excel as MaatwebsiteExcel;
use Sheba\Business\Payslip\PayReport\PayReportDetails;
use Sheba\Dal\Payslip\PayslipRepository;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PayReportController extends Controller
{
    use FileManager, CdnFileManager;

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
     * @param BusinessMemberRepositoryInterface $business_member_repo
     * @return JsonResponse|BinaryFileResponse
     */
    public function index( $business, $business_payslip_id ,Request $request, PayReportList $pay_report_list, PaySlipExcel $pay_slip_excel)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $payroll_setting = $business->payrollSetting;
        list($offset, $limit) = calculatePagination($request);
        $payslip = $pay_report_list->setBusiness($business)
            ->setBusinessPayslipId($business_payslip_id)
            ->setSearch($request->search)
            ->setSortKey($request->sort)
            ->setSortColumn($request->sort_column)
            //->setMonthYear($request->month_year)
            ->setDepartmentID($request->department_id)
            ->setGrossSalaryProrated($request->gross_salary_prorated)
            ->get();
        $count = count($payslip);
        if ($request->file == 'excel') {
            $excel = new PayslipExcel($business_member_repo, $payslip->toArray());
            return MaatwebsiteExcel::download($excel, 'Pay_report.xlsx');
        }
        if ($request->limit == 'all') $limit = $count;
        $payslip = collect($payslip)->splice($offset, $limit);

        return api_response($request, null, 200, [
            'payslip' => $payslip,
            'total_calculation' => $pay_report_list->getTotal(),
            'total' => $count,
            'is_prorated_filter_applicable' => $pay_report_list->getIsProratedFilterApplicable(),
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
     * @throws \Exception
     */
    public function bkashSalaryReport(Request $request, PayReportList $pay_report_list): JsonResponse
    {
        /** @var Business $business */
        $business = $request->business;

        $payslip = $pay_report_list->setBusiness($business)
            ->setMonthYear($request->month_year)
            ->getBkashSalaryData();

        $six_digit_random_number = randomString(6, true);
        $time = Carbon::now()->format("Y_m_d_H_i_s");
        $file_name = 'Net_Payable_Bkash_Report_' . $six_digit_random_number . '_' . $time . ".xlsx";
        $excel = new BkashSalaryReportExcel($payslip->toArray());
        MaatwebsiteExcel::store($excel, $file_name);

        $file_path = storage_path("app/" . $file_name) . DIRECTORY_SEPARATOR . $file_name;
        $file_name = $this->uniqueFileName($file_path, $file_name, 'xlsx');
        $file_link = $this->saveFileToCDN($file_path, getBulkGrossSalaryFolder(), $file_name);
        unlink($file_path);

        return api_response($request, null, 200, ['bkash_salary_report' => $file_link]);
    }
}
