<?php namespace App\Http\Controllers\B2b;


use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\SubscriptionOrder;
use Illuminate\Http\Request;
use Sheba\Payment\Adapters\Payable\SubscriptionOrderAdapter;
use Sheba\Payment\ShebaPayment;

class SubscriptionOrderController extends Controller
{

    public function clearPayment($subscription_order, Request $request, ShebaPayment $sheba_payment)
    {
        try {
            $this->validate($request, ['payment_method' => 'required|string|in:bkash,wallet,cbl,online']);
            $payment_method = $request->payment_method;
            /** @var Business $business */
            $business = $request->business;
            $member = $request->manager_member;
            /** @var SubscriptionOrder $subscription_order */
            $subscription_order = SubscriptionOrder::find((int)$subscription_order);
            if ($payment_method == 'wallet' && $subscription_order->getTotalPrice() > $business->shebaCredit()) return api_response($request, null, 403, ['message' => 'You don\'t have sufficient credit.']);
            $order_adapter = new SubscriptionOrderAdapter();
            $payable = $order_adapter->setModelForPayable($subscription_order)->getPayable();
            $payment = $sheba_payment->setMethod($payment_method)->init($payable);
        } catch (\Throwable $e) {

        }
    }
}