<?php namespace App\Http\Controllers;

use Sheba\Dal\PartnerOrderPayment\PartnerOrderPayment;
use App\Models\Resource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;

class PartnerOrderPaymentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $partner = $request->partner;
            $year = $request->year;
            $month = $request->month;
            $date = $request->date;
            $collections = PartnerOrderPayment::Join('partner_orders', 'partner_orders.id', '=', 'partner_order_payments.partner_order_id')
                ->join('partners', 'partner_orders.partner_id', '=', 'partners.id')
                ->where('partner_order_payments.created_by_type', 'App\\Models\\Resource')->when($year, function ($query) use ($year) {
                    return $query->where(DB::raw('YEAR(partner_order_payments.created_at)'), $year);
                })->when($month, function ($query) use ($month) {
                    return $query->where(DB::raw('MONTH(partner_order_payments.created_at)'), $month);
                })->when($date, function ($query) use ($date, $month, $year) {
                    return $query->where(DB::raw('DATE(partner_order_payments.created_at)'), (Carbon::createFromDate($year, $month, $date))->format('Y-m-d'));
                })
                ->with('partnerOrder')
                ->where('partners.id', $partner->id)->orderBy('partner_order_payments.id', 'desc')
                ->select('partner_order_payments.id', 'partner_order_payments.created_at', 'partner_order_payments.created_by', 'partner_order_payments.amount', 'log', 'method', DB::raw('partner_orders.id as partner_order_id'))
                ->get();
            $collections = $this->getCollectionInformation($collections);
            if ($request->filled('groupBy')) {
                $collections = $this->getGroupedCollectionInformation($collections);
            }
            $total = $collections->sum('amount');
            return count($collections) > 0 ? api_response($request, $total, 200, ['collections' => $collections, 'total' => $total]) : api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getCollectionInformation($collections)
    {
        $collections->each(function (&$collection) {
            $profile = (Resource::find($collection->created_by))->profile;
            $created_at_timestamp = $collection->created_at->timestamp;
            $collection['resource_name'] = $profile->name;
            $collection['resource_mobile'] = $profile->mobile;
            $collection['resource_picture'] = $profile->pro_pic;
            $collection['resource_id'] = $collection->created_by;
            $collection['created_at_timestamp'] = $created_at_timestamp;
            $collection['code'] = $collection->partnerOrder->code();
            $collection['version'] = $collection->partnerOrder->getVersion();
            removeRelationsAndFields($collection);
        });
        return $collections;
    }

    private function getGroupedCollectionInformation($collections)
    {
        $resources = $collections->groupBy('resource_name');
        $final = [];
        foreach ($resources as $resource) {
            array_push($final, array(
                'resource_name' => $resource[0]->resource_name,
                'resource_mobile' => $resource[0]->resource_mobile,
                'resource_picture' => $resource[0]->resource_picture,
                'total_collected_times' => count($resource),
                'amount' => $resource->sum('amount')
            ));
        }
        return collect($final);
    }

}
