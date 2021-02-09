<?php namespace App\Http\Controllers\B2b;


use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Business;
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
     * @return JsonResponse
     */
    public function index(Request $request, PayrunList $payrun_list)
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

        $payslip = collect($payslip)->splice($offset, $limit);

        return api_response($request, null, 200, ['payslip' => $payslip, 'total' => $count]);

    }

    /**
     * @param Request $request
     * @param PendingMonths $pendingMonths
     */
    public function pendingMonths(Request $request, PendingMonths $pendingMonths)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;

        if (!$business_member) return api_response($request, null, 401);

        $pending_months = $pendingMonths->setBusiness($business)->get();

        return api_response($request, null, 200, ['pending_months' => $pending_months]);
    }
}
