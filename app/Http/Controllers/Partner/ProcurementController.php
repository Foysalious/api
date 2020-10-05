<?php namespace App\Http\Controllers\Partner;

use App\Models\Procurement;
use Illuminate\Http\Request;
use Sheba\ModificationFields;
use App\Http\Controllers\Controller;
use Sheba\Business\Procurement\Creator;
use App\Sheba\Business\Procurement\Updater;
use App\Sheba\Business\Procurement\ProcurementOrder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Sheba\Repositories\Interfaces\BidRepositoryInterface;

class ProcurementController extends Controller
{
    use ModificationFields;

    /**
     * @param $partner
     * @param $procurement
     * @param Request $request
     * @param Updater $updater
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus($partner, $procurement, Request $request, Updater $updater)
    {
        $this->validate($request, [
            'status' => 'required|string',
        ]);
        $this->setModifier($request->manager_resource);
        $procurement = Procurement::find((int)$procurement);
        if (!$procurement) {
            return api_response($request, null, 404);
        } else {
            $updater->setProcurement($procurement)->setStatus($request->status)->updateStatus();
            return api_response($request, null, 200);
        }
    }

    /**
     * @param $partner
     * @param $procurement
     * @param Request $request
     * @param ProcurementOrder $procurement_order
     * @return \Illuminate\Http\JsonResponse
     */
    public function orderTimeline($partner, $procurement, Request $request, ProcurementOrder $procurement_order)
    {
        $order_timelines = $procurement_order->setProcurement($procurement)->getBid()->formatTimeline();
        return api_response($request, $order_timelines, 200, ['timelines' => $order_timelines]);
    }

    /**
     * @param $partner
     * @param $procurement
     * @param $bid
     * @param Request $request
     * @param ProcurementOrder $procurement_order
     * @param BidRepositoryInterface $bid_repository
     * @return \Illuminate\Http\JsonResponse
     */
    public function showProcurementOrder($partner, $procurement, $bid, Request $request, ProcurementOrder $procurement_order, BidRepositoryInterface $bid_repository)
    {
        $bid = $bid_repository->find((int)$bid);
        if (!$bid) return api_response($request, null, 404);
        $rfq_order_details = $procurement_order->setProcurement($procurement)->setBid($bid)->orderDetails();
        return api_response($request, null, 200, ['order_details' => $rfq_order_details]);
    }

    /**
     * @param $partner
     * @param $procurement
     * @param Request $request
     * @param Creator $creator
     * @return \Illuminate\Http\JsonResponse
     */
    public function orderBill($partner, $procurement, Request $request, Creator $creator)
    {
        $procurement = Procurement::findOrFail((int)$procurement);
        $procurement->calculate();
        $rfq_order_bill['total_price'] = $procurement->getActiveBid()->price;
        $rfq_order_bill['paid'] = $procurement->paid;
        $rfq_order_bill['due'] = $procurement->due;
        return api_response($request, $rfq_order_bill, 200, ['rfq_order_bill' => $rfq_order_bill]);
    }
}