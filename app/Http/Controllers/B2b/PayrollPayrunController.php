<?php namespace App\Http\Controllers\B2b;


use App\Http\Controllers\Controller;
use App\Http\Requests\Request;
use App\Models\BusinessMember;
use Sheba\Dal\Payslip\PayslipRepository;

class PayrollPayrunController extends Controller
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
    /** @param Request $request */
    public function index(Request $request)
    {
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);

        list($offset, $limit) = calculatePagination($request);
    }
}
