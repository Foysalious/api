<?php namespace App\Http\Controllers\B2b;

use App\Exceptions\DoNotReportException;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Member;
use App\Sheba\Business\Payslip\Excel as PaySlipExcel;
use App\Sheba\Business\Payslip\PayRun\PayRunBulkExcel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\BusinessMember;
use Sheba\Business\Payslip\PayRun\Updater as PayRunUpdater;
use Sheba\Dal\AuthenticationRequest\Purpose;
use Sheba\Dal\PayrollComponent\Type;
use Sheba\Dal\Payslip\PayslipRepository;
use App\Sheba\Business\Payslip\PayrunList;
use App\Sheba\Business\Payslip\PendingMonths;
use Sheba\ModificationFields;
use Sheba\OAuth2\AccountServerAuthenticationError;
use Sheba\OAuth2\AccountServerNotWorking;
use Sheba\OAuth2\WrongPinError;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\TopUp\Exception\PinMismatchException;
use Sheba\OAuth2\VerifyPin;

class PayRunController extends Controller
{
    use ModificationFields;

    private $payslipRepo;
    private $businessMemberRepository;
    private $payrunUpdater;

    /**
     * PayRunController constructor.
     * @param PayslipRepository $payslip_repo
     * @param BusinessMemberRepositoryInterface $business_member_repository
     * @param PayRunUpdater $payrun_updater
     */
    public function __construct(PayslipRepository $payslip_repo, BusinessMemberRepositoryInterface $business_member_repository, PayRunUpdater $payrun_updater)
    {
        $this->payslipRepo = $payslip_repo;
        $this->businessMemberRepository = $business_member_repository;
        $this->payrunUpdater = $payrun_updater;
    }

    /**
     * @param Request $request
     * @param PayrunList $payrun_list
     * @param PaySlipExcel $pay_slip_excel
     * @param PayRunBulkExcel $pay_run_bulk_excel
     * @return JsonResponse
     */
    public function index(Request $request, PayrunList $payrun_list, PaySlipExcel $pay_slip_excel, PayRunBulkExcel $pay_run_bulk_excel)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        list($offset, $limit) = calculatePagination($request);
        $payslip = $payrun_list->setBusiness($business)
            ->setMonthYear($request->month_year)
            ->setDepartmentID($request->department_id)
            ->setSearch($request->search)
            ->setSortKey($request->sort)
            ->setSortColumn($request->sort_column)
            ->get();

        $count = count($payslip);
        if ($request->file == 'excel') return $pay_slip_excel->setPayslipData($payslip->toArray())->setPayslipName('Pay_run')->get();
        if ($request->limit == 'all') $limit = $count;

        $payroll_components = $business->payrollSetting->components->whereIn('type', [Type::ADDITION, Type::DEDUCTION])->sortBy('type');
        if ($request->generate_sample) $pay_run_bulk_excel->setBusiness($business)->setPayslips($payslip)->setPayrollComponent($payroll_components)->get();
        
        $payslip = collect($payslip)->splice($offset, $limit);

        return api_response($request, null, 200, ['total_calculation' => $payrun_list->getTotal(), 'payslip' => $payslip, 'payroll_components' => $payrun_list->getComponents($payroll_components), 'total' => $count]);
    }

    /**
     * @param Request $request
     * @param PendingMonths $pending_months
     * @return JsonResponse
     */
    public function pendingMonths(Request $request, PendingMonths $pending_months)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $payroll_setting = $business->payrollSetting;
        $get_pending_months = $pending_months->setBusiness($business)->get();
        return api_response($request, null, 200, ['is_enable' => $payroll_setting->is_enable, 'pending_months' => $get_pending_months]);
    }

    /**
     * @param Request $request
     * @param VerifyPin $verifyPin
     * @return JsonResponse
     * @throws DoNotReportException
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking
     * @throws WrongPinError
     * @throws PinMismatchException
     */
    public function disburse(Request $request, VerifyPin $verifyPin)
    {
        $this->validate($request, [
            'schedule_date' => 'required|date|date_format:Y-m'
        ]);
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        /** @var Member $manager_member */
        $manager_member = $request->manager_member;
        $this->setModifier($manager_member);

        $verifyPin->setAgent($business)->setProfile($request->access_token->authorizationRequest->profile)->setRequest($request)->setPurpose(Purpose::PAYSLIP_DISBURSE)->verify();
        $this->payrunUpdater->setScheduleDate($request->schedule_date)->setBusiness($business)->disburse();

        return api_response($request, null, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkUpdate(Request $request)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        /** @var Member $manager_member */
        $manager_member = $request->manager_member;
        $this->setModifier($manager_member);

        $this->payrunUpdater->setData($request->data)->setManagerMember($manager_member)->update();

        return api_response($request, null, 200);
    }
}
