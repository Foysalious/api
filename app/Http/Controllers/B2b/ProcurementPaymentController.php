<?php namespace App\Http\Controllers\B2b;


use App\Http\Controllers\Controller;
use App\Models\Procurement;
use App\Sheba\Business\Procurement\Updater;
use Illuminate\Http\Request;
use Sheba\Business\Procurement\OrderClosedHandler;
use Sheba\Business\ProcurementPayment\Creator;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;
use DB;

class ProcurementPaymentController extends Controller
{
    use ModificationFields;

    public function adjustPayment($business, $procurement, Request $request, Creator $payment_creator, Updater $procurement_updater, ProcurementRepositoryInterface $procurement_repository, OrderClosedHandler $procurement_order_close_handler)
    {
        $this->validate($request, ['payment_method' => 'required|string', 'sheba_collection' => 'required|numeric']);
        $this->setModifier($request->manager_member);
//        /** @var Procurement $procurement */
//        $procurement = $procurement_repository->where('id', $procurement)->where('owner_id', $business)
//            ->where('owner_type', 'like', '%business')->first();
//        if (!$procurement) return api_response($request, 1, 404);
//        $procurement->calculate();
//        if ((double)$request->sheba_collection > $procurement->due) return api_response($request, 1, 403, ['message' => "Can't collect more than due"]);
//        DB::transaction(function () use ($request, $procurement_updater, $procurement, $payment_creator, $procurement_order_close_handler) {
//            $payment_creator->setAmount($request->sheba_collection)->setPaymentMethod($request->payment_method)->setPaymentType('Debit')
//                ->setLog("Adjusted payment from Sheba Admin")->setProcurement($procurement)->create();
//            $procurement_updater->setProcurement($procurement)->setShebaCollection($procurement->sheba_collection + $request->sheba_collection)->update();
//            $procurement_order_close_handler->setProcurement($procurement->fresh())->run();
//        });
        return api_response($request, 1, 200);
    }
}