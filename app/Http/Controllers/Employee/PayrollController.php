<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Sheba\Business\BusinessBasicInformation;
use Illuminate\Support\Facades\App;
use Sheba\Business\Payslip\PayReport\PayReportDetails;
use Sheba\Dal\Payslip\PayslipRepository;
use Sheba\Helpers\TimeFrame;

class PayrollController extends Controller
{
    use BusinessBasicInformation;

    public function downloadPayslip(Request $request, PayReportDetails $pay_report_details, PayslipRepository $payslip_repository, TimeFrame $time_frame)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $time_period = $time_frame->forAMonth($request->month, $request->year);
        $payslip = $payslip_repository->where('business_member_id', $business_member->id)->whereBetween('schedule_date', [$time_period->start, $time_period->end])->first();
        if (!$payslip) return api_response($request, null, 404);
        $pay_report_detail = $pay_report_details->setPayslip($payslip)->get();
        return App::make('dompdf.wrapper')->loadView('pdfs.payslip.payroll_details', compact('pay_report_detail'))->download("payroll_details.pdf");
    }

}