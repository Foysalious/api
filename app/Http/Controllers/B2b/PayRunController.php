<?php namespace App\Http\Controllers\B2b;


use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Sheba\Business\Payslip\Excel as PaySlipExcel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\BusinessMember;
use Sheba\Dal\Payslip\PayslipRepository;
use App\Sheba\Business\Payslip\PayrunList;
use App\Sheba\Business\Payslip\PendingMonths;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class PayRunController extends Controller
{
    /**
     * @var PayslipRepository
     */
    private $payslipRepo;
    private $businessMemberRepository;

    /**
     * PayRunController constructor.
     * @param PayslipRepository $payslip_repo
     * @param BusinessMemberRepositoryInterface $business_member_repository
     */
    public function __construct(PayslipRepository $payslip_repo,
                                BusinessMemberRepositoryInterface $business_member_repository)
    {
        $this->payslipRepo = $payslip_repo;
        $this->businessMemberRepository = $business_member_repository;
    }

    /**
     * @param Request $request
     * @param PayrunList $payrun_list
     * @param PaySlipExcel $pay_slip_excel
     * @return JsonResponse
     */
    public function index(Request $request, PayrunList $payrun_list, PaySlipExcel $pay_slip_excel)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;

        if (!$business_member) return api_response($request, null, 401);

        $payroll_setting = $business->payrollSetting;

        list($offset, $limit) = calculatePagination($request);

        $payslip = $payrun_list->setBusiness($business)
            ->setMonthYear($request->month_year)
            ->setDepartmentID($request->department_id)
            ->setSearch($request->search)
            ->setSortKey($request->sort)
            ->setSortColumn($request->sort_column)
            ->get();

        $count = count($payslip);

        if($request->limit == 'all') $limit = $count;
        $payslip = collect($payslip)->splice($offset, $limit);

        if ($request->file == 'excel') return $pay_slip_excel->setPayslipData($payslip->toArray())->setPayslipName('Pay_run')->get();

        return api_response($request, null, 200, ['is_enable' => $payroll_setting->is_enable, 'payslip' => $payslip, 'total' => $count]);

    }


    /**
     * @param Request $request
     * @param PendingMonths $pendingMonths
     * @return JsonResponse
     */
    public function pendingMonths(Request $request, PendingMonths $pending_months)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;

        if (!$business_member) return api_response($request, null, 401);

        $get_pending_months = $pending_months->setBusiness($business)->get();

        return api_response($request, null, 200, ['pending_months' => $get_pending_months]);
    }
}
