<?php namespace App\Http\Controllers\B2b;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\BusinessMember;
use Sheba\Dal\Payslip\PayslipRepository;
use App\Sheba\Business\Payslip\PayrunList;

class PayrunController extends Controller
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
}
