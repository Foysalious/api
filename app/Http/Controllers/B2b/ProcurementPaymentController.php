<?php namespace App\Http\Controllers\B2b;


use App\Http\Controllers\Controller;
use App\Models\Procurement;
use App\Sheba\Business\Procurement\Updater;
use Illuminate\Http\Request;
use Sheba\Business\Procurement\OrderClosedHandler;
use Sheba\Business\ProcurementPayment\Creator;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;

class ProcurementPaymentController extends Controller
{
    public function adjustPayment($business, $procurement, Request $request, Creator $payment_creator, Updater $procurement_updater, ProcurementRepositoryInterface $procurement_repository, OrderClosedHandler $procurement_order_close_handler)
    {
        $this->validate($request, ['payment_method' => 'required|string']);
        /** @var Procurement $procurement */
        $procurement = $procurement_repository->find($procurement);
        $procurement->calculate();
        $payment_creator->setAmount($procurement->due)->setPaymentMethod($request->payment_method)->setPaymentType('Debit')->setProcurement($procurement)->create();
        $procurement_updater->setProcurement($procurement)->setShebaCollection($procurement->sheba_collection + $procurement->due)->update();
        $procurement_order_close_handler->setProcurement($procurement->fresh())->run();
    }
}