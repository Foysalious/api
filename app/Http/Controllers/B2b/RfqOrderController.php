<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\Procurement;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Business\ProcurementOrder\Creator;
use Sheba\ModificationFields;
use Sheba\Repositories\Business\RfqOrderRepository;
use Sheba\Repositories\Interfaces\BidRepositoryInterface;

class RfqOrderController extends Controller
{
    use ModificationFields;

    public function index($business, Request $request, RfqOrderRepository $rfq_order_repository)
    {
        try {
            list($offset, $limit) = calculatePagination($request);
            $procurements = Procurement::order()->with(['bids' => function ($q) {
                $q->select('id', 'procurement_id', 'bidder_id', 'bidder_type', 'price');
            }])->orderBy('id', 'DESC');
            if ($request->has('status')) {
                $procurements = $procurements->where('status', $request->status);
            }

            $start_date = $request->has('start_date') ? $request->start_date : null;
            $end_date = $request->has('end_date') ? $request->end_date : null;
            if ($start_date && $end_date) {
                $procurements->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
            }
            $procurements = $procurements->skip($offset)->limit($limit)->get();
            $rfq_order_lists = [];
            foreach ($procurements as $procurement) {
                $bid = $procurement->bids ? $procurement->bids->first() : null;
                array_push($rfq_order_lists, [
                    'procurement_id' => $procurement->id,
                    'procurement_status' => $procurement->status,
                    'color' => constants('PROCUREMENT_ORDER_STATUSES_COLOR')[$procurement->status],
                    'bid_id' => $bid ? $bid->id : null,
                    'price' => $bid ? $bid->price : null,
                    'vendor' => [
                        'name' => $bid ? $bid->bidder->name : null
                    ]
                ]);
            }

            return api_response($request, $rfq_order_lists, 200, ['rfq_order_lists' => $rfq_order_lists]);
        } catch (ModelNotFoundException $e) {
            return api_response($request, null, 404, ["message" => "Model Not found."]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show($business, $procurement, $bid, Request $request, RfqOrderRepository $rfq_order_repository)
    {
        try {
            $procurement = Procurement::findOrFail((int)$procurement);
            $bid = Bid::findOrFail((int)$bid);
            $rfq_order_details = $rfq_order_repository->setProcurement($procurement)->setBid($bid)->formatData();
            return api_response($request, $rfq_order_details, 200, ['order_details' => $rfq_order_details]);
        } catch (ModelNotFoundException $e) {
            return api_response($request, null, 404, ["message" => "Model Not found."]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}