<?php namespace App\Http\Controllers\Partner;

use Sheba\Repositories\Interfaces\BidRepositoryInterface;
use Sheba\Repositories\Business\RfqOrderRepository;
use App\Http\Controllers\Controller;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use App\Models\Procurement;
use App\Models\Bid;

class RfqOrderController extends Controller
{
    use ModificationFields;

    public function show($partner, $procurement, $bid, Request $request, RfqOrderRepository $rfq_order_repository)
    {
        try {
            $procurement = Procurement::findOrFail((int)$procurement);
            if (!$procurement) {
                return api_response($request, null, 404, ["message" => "Procurement Not found."]);
            } else {
                /** @var Bid $bid */
                $bid = Bid::findOrFail((int)$bid);
                if (!$bid) {
                    return api_response($request, null, 404, ["message" => "Bid Not found."]);
                } else {
                    $rfq_order_details = $rfq_order_repository->setProcurement($procurement)->setBid($bid)->formatData();
                    return api_response($request, $rfq_order_details, 200, ['order_details' => $rfq_order_details]);
                }
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}