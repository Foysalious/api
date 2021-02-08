<?php namespace App\Http\Controllers\B2b;


use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Sheba\Business\Payslip\PayreportList;
use Illuminate\Http\Request;
use Sheba\Dal\Payslip\PayslipRepository;

class PayreportController extends Controller
{
    /**
     * @var PayslipRepository
     */
    private $PayslipRepo;

    /**
     * PayrollPayrunController constructor.
     * @param PayslipRepository $payslip_repo
     */
    public function __construct(PayslipRepository $payslip_repo)
    {
        $this->PayslipRepo = $payslip_repo;
    }

    /**
     * @param Request $request
     * @param PayreportList $payreportlist
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, PayreportList $payreportlist)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;

        if (!$business_member) return api_response($request, null, 401);

        list($offset, $limit) = calculatePagination($request);

        $payslip = $payreportlist->setBusiness($business)
            ->setSearch($request->search)
            ->setSortKey($request->sort)
            ->setSortColumn($request->sort_column)
            ->get();

        $count = count($payslip);

        return api_response($request, null, 200, ['payslip' => $payslip, 'total' => $count]);

    }
}
