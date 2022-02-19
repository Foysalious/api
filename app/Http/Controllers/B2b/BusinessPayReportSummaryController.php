<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Transformers\Business\PayReportSummaryListTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\ArraySerializer;

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
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $payreport_summary_list_transformer = new PayReportSummaryListTransformer();
        $payreport_summary_list = new Collection($payslip_summary, $payreport_summary_list_transformer);
        $payreport_summary_list = collect($manager->createData($payreport_summary_list)->toArray()['data']);
        return api_response($request, null, 200, ['pay_report_summary' => $payreport_summary_list]);
    }
}
