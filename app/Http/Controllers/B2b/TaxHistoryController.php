<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Sheba\Business\Payslip\TaxHistoryList;
use Sheba\Helpers\TimeFrame;

class TaxHistoryController extends Controller
{
    /*** @var TimeFrame $timeFrame*/
    private $timeFrame;

    public function __construct(TimeFrame $time_frame)
    {
        $this->timeFrame = $time_frame;
    }

    public function index(Request $request, TaxHistoryList $tax_history_list)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        list($offset, $limit) = calculatePagination($request);
        $time_period = $this->timeFrame->forAMonth($request->month, $request->year);
        $tax_report = $tax_history_list->setBusiness($business)->setTimePeriod($time_period)->get();
        $total_report_count = $tax_report->count();
        $total_tax_amount = $tax_report->sum('total_tax_amount_monthly');
        $tax_report = collect($tax_report)->splice($offset, $limit);
        return api_response($request, null, 200, ['tax_history' => $tax_report, 'total_tax_amount' => $total_tax_amount, 'show_download_report_banner' => 1, 'total' => $total_report_count]);
    }

}