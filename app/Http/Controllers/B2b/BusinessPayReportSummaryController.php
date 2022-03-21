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
        list($offset, $limit) = calculatePagination($request);
        $payslip_summary = $business->payslipSummary;
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $payreport_summary_list_transformer = new PayReportSummaryListTransformer();
        $payreport_summary_list = new Collection($payslip_summary, $payreport_summary_list_transformer);
        $payreport_summary_list = collect($manager->createData($payreport_summary_list)->toArray()['data']);
        $list_count = count($payreport_summary_list);
        if ($request->limit == 'all') $limit = $list_count;
        $payreport_summary_list = collect($payreport_summary_list)->splice($offset, $limit);

        if ($request->has('sort')){
            $payreport_summary_list = $this->sortByColumn( collect($payreport_summary_list), $request->sort, $request->sort_order)->values()->toArray();
        }
        return api_response($request, null, 200, ['pay_report_summary' => $payreport_summary_list, 'total' => $list_count]);
    }

    private function sortByColumn($data, $sort, $order)
    {
        $sort_by = ($order === 'asc') ? 'sortBy' : 'sortByDesc';
        return $data->$sort_by(function ($item, $key) use ($sort){
            return $item[$sort];
        });
    }
}
