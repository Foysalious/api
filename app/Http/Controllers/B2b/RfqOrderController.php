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