<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Sheba\Business\Payslip\Excel as PaySlipExcel;
use App\Sheba\Business\Payslip\PayReportList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        $payslip = $pay_report_list->setBusiness($business)
            ->setSearch($request->search)
            ->setSortKey($request->sort)
            ->setSortColumn($request->sort_column)
            ->setMonthYear($request->month_year)
            ->setDepartmentID($request->department_id)
            ->get();

        $count = count($payslip);

        if ($request->file == 'excel') return $pay_slip_excel->setPayslipData($payslip->toArray())->setPayslipName('Pay_report')->get();

        if($request->limit == 'all') $limit = $count;
        $payslip = collect($payslip)->splice($offset, $limit);

        return api_response($request, null, 200, ['is_enable' => $payroll_setting->is_enable, 'payslip' => $payslip, 'total' => $count]);

    }
}
