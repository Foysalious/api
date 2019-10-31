<?php namespace App\Http\Controllers\Partner;

use Illuminate\Database\Eloquent\ModelNotFoundException;
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