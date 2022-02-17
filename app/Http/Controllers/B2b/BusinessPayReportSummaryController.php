<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessMember;
use Illuminate\Http\Request;

class BusinessPayReportSummaryController extends Controller
{
    public function index(Request $request)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $payslip_summary = $business->payslipSummary;
    }
}
