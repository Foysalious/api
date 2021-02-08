<?php namespace App\Http\Controllers\B2b;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\BusinessMember;
use Sheba\Dal\Payslip\PayslipRepository;
use App\Sheba\Business\Payslip\PayrunList;
use App\Sheba\Business\Payslip\PendingMonths;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class PayrunController extends Controller
{
    /**
     * @var PayslipRepository
     */
    private $PayslipRepo;
    private $businessMemberRepository;

    /**
     * PayrollPayrunController constructor.
     * @param PayslipRepository $payslip_repo
     * @param BusinessMemberRepositoryInterface $business_member_repository
     */
    public function __construct(PayslipRepository $payslip_repo,
                                BusinessMemberRepositoryInterface $business_member_repository)
    {
        $this->PayslipRepo = $payslip_repo;
        $this->businessMemberRepository = $business_member_repository;
    }

    /**
     * @param Request $request
     * @param PayrunList $payrunlist
     */
    public function index(Request $request, PayrunList $payrunlist)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;

        if (!$business_member) return api_response($request, null, 401);

        list($offset, $limit) = calculatePagination($request);

        $payslip = $payrunlist->setBusiness($business)->get();

        $count = count($payslip);

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
        $pending_months = $pendingMonths->setBusiness($business)->get();

        return api_response($request, null, 200, ['pending_months' => $pending_months]);
    }
}
