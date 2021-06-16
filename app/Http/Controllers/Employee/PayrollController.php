<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Sheba\Business\Payslip\PayReport\PayReportPdfHandler;
use Illuminate\Http\Request;
use App\Sheba\Business\BusinessBasicInformation;
use Sheba\Business\Payslip\PayReport\PayReportDetails;
use Sheba\Dal\Payslip\PayslipRepository;
use Sheba\Dal\Payslip\Status;
use Sheba\Helpers\TimeFrame;

class PayrollController extends Controller
{
    use BusinessBasicInformation;

    /*** @var PayslipRepository */
    private $payslipRepository;

    public function __construct(PayslipRepository $payslip_repository)
    {
        $this->payslipRepository = $payslip_repository;
    }

    public function downloadPayslip(Request $request, PayReportDetails $pay_report_details, TimeFrame $time_frame, PayReportPdfHandler $pay_report_pdf_handler)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $time_period = $time_frame->forAMonth($request->month, $request->year);
        $payslip = $this->payslipRepository->where('business_member_id', $business_member->id)->where('status', Status::DISBURSED)->whereBetween('schedule_date', [$time_period->start, $time_period->end])->first();
        if (!$payslip) return api_response($request, null, 404);
        $pay_report_detail = $pay_report_details->setPayslip($payslip)->get();

        $pay_report_pdf = $pay_report_pdf_handler->setPayReportDetails($pay_report_detail)->setTimePeriod($time_period)->generate();
        return api_response($request, null, 200, ['payslip_pdf_link' => $pay_report_pdf]);
    }

    public function disbursedMonth(Request $request)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $disbursed_payslips = $this->payslipRepository->where('business_member_id', $business_member->id)->where('status', Status::DISBURSED)->orderBy('schedule_date', 'DESC')->get();
        if (!$disbursed_payslips) return api_response($request, null, 404);
        $disbursed_months_data = [];
        foreach ($disbursed_payslips as $disbursed_payslip) {
            $schedule_date = $disbursed_payslip->schedule_date;
            array_push($disbursed_months_data, [
                'id' => $disbursed_payslip->id,
                'year' => $schedule_date->format('Y'),
                'month' => $schedule_date->format('m'),
                'day' => $schedule_date->format('d'),
                'month_name' => $schedule_date->format('M'),
            ]);
        }
        return api_response($request, null, 200, ['disbursed_months' => $disbursed_months_data]);
    }
}