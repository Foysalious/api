<?php namespace App\Http\Controllers;

use App\Jobs\CalculatePapAffiliateId;
use App\Models\Customer;
use App\Repositories\CartRepository;
use App\Repositories\CheckoutRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\DiscountRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\VoucherRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Cache;
use DB;
use Mail;
use Illuminate\Support\Facades\Redis;

class CheckoutController extends Controller
{
    use DispatchesJobs;
    private $checkoutRepository;
    private $voucherRepository;
    private $cartRepository;
    private $fbKit;
    private $customer;

    public function __construct()
    {
        $this->checkoutRepository = new CheckoutRepository();
        $this->fbKit = new FacebookAccountKit();
        $this->customer = new CustomerRepository();
        $this->voucherRepository = new VoucherRepository();
        $this->cartRepository = new CartRepository();
    }

    public function placeOrder(Request $request, $customer)
    {
        array_add($request, 'customer_id', $customer);
        $cart = json_decode($request->cart);
        $cart->items = $this->cartRepository->checkValidation($cart, $request->location_id);
        if ($cart->items[0] == false) {
            return response()->json(['code' => 400, 'msg' => $cart->items[1]]);
        }
        $request->merge(array('cart' => json_encode($cart)));
        //store order details for customer
        $order = $this->checkoutRepository->storeDataInDB($request->all(), 'cash-on-delivery');
        if ($order) {
            $customer = Customer::find($order->customer_id);
            $this->updateVouchers($order, $customer);
            $this->checkoutRepository->sendConfirmation($customer->id, $order);
            if ($order->pap_visitor_id != null) {
                $this->dispatch(new CalculatePapAffiliateId($order));
            }
            $order->calculate();
            return response()->json(['code' => 200, 'pap_number' => (double)$order->profit, 'pap_code' => $order->code(), 'msg' => 'Order placed successfully!']);
        } else {
            return response()->json(['code' => 500, 'msg' => 'There is a problem while placing the order!']);
        }
    }

    /**
     * Place with order with payment
     * @param Request $request
     * @param $customer
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeOrderWithPayment(Request $request, $customer)
    {
        $customer = $request->customer;
        $connectionResponse = $this->checkoutRepository->checkoutWithPortWallet($request, $customer);
        return response()->json($connectionResponse);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function placeOrderFinal(Request $request)
    {
        $portwallet = $this->checkoutRepository->getPortWalletObject();
        $order_info = Cache::get('portwallet-payment-' . $request->input('invoice'));
        $cart = json_decode($order_info['cart']);

        $data = array();
        $data["amount"] = $this->checkoutRepository->getTotalCartAmount($cart);
        $data["invoice"] = Cache::get('invoice-' . $request->input('invoice'));
        $data['currency'] = "BDT";
        $portwallet_response = $portwallet->ipnValidate($data);
        //check payment validity
        if ($portwallet_response->status == 200 && $portwallet_response->data->status == "ACCEPTED") {
            $order_info['portwallet_response'] = $portwallet_response;
            $order = $this->checkoutRepository->storeDataInDB($order_info, 'online');
            if ($order) {
                $customer = Customer::find($order->customer_id);
                $this->updateVouchers($order, $customer);
                $this->checkoutRepository->sendConfirmation($order_info['customer_id'], $order);
                Cache::forget('invoice-' . $request->input('invoice'));
                Cache::forget('portwallet-payment-' . $request->input('invoice'));
                if (array_key_exists('device_token', $order_info)) {
                    $this->checkoutRepository->sendOnlinePaymentNotificationToDevice($order_info['device_token'], true);
                } else {
                    $s_id = str_random(10);
                    Redis::set($s_id, 'online');
                    Redis::expire($s_id, 500);
                    $order->calculate();
                    return redirect(env('SHEBA_FRONT_END_URL') . '/order-list?s_token=' . $s_id . '&pap_number=' . (double)$order->profit . '&pap_code=' . $order->code());
                }
            }
        } else {
            if (array_key_exists('device_token', $order_info)) {
                $this->checkoutRepository->sendOnlinePaymentNotificationToDevice($order_info['device_token'], false);
            } else {
                return "Something went wrong";
            }
        }
    }

    private function updateVouchers($order, $customer)
    {
        if ($order->voucher_id == null) return;
        $voucher = $order->voucher;
        $this->updateVoucherInPromoList($customer, $voucher, $order);
    }

    public function spPayment(Request $request, $customer)
    {
        $customer = Customer::find($customer);
        $connectionResponse = $this->checkoutRepository->spPaymentWithPortWallet($request, $customer);
        return response()->json($connectionResponse);
    }

    public function spPaymentFinal(Request $request)
    {
        $portwallet = $this->checkoutRepository->getPortWalletObject();
        $payment_info = Cache::get('portwallet-payment-' . $request->input('invoice'));
        $data = array();
        $data["amount"] = $payment_info['price'];
        $data["invoice"] = Cache::get('invoice-' . $request->input('invoice'));
        $data['currency'] = "BDT";
        $portwallet_response = $portwallet->ipnValidate($data);
        //check payment validity
        if ($portwallet_response->status == 200 && $portwallet_response->data->status == "ACCEPTED") {
            $response = $this->checkoutRepository->clearSpPayment($payment_info, $portwallet_response);
            if ($response) {
                if ($response->code == 200) {
                    (new NotificationRepository())->forOnlinePayment($payment_info['partner_order_id'][0], $payment_info['price']);
                    Cache::forget('invoice-' . $request->input('invoice'));
                    Cache::forget('portwallet-payment-' . $request->input('invoice'));
                    return redirect(env('SHEBA_FRONT_END_URL') . '/order-list');
                }
            } else {
                return "Something went wrong";
            }
        } else {
            return "Something went wrong";
        }
    }

    public function validateVoucher(Request $request)
    {
        $data = json_decode($request->data);
        $sales_channel = property_exists($data, 'sales_channel') ? $data->sales_channel : "Web";
        $cart = $data->cart;
        if ($this->cartRepository->hasDiscount($cart->items)) {
            return api_response($request, null, 404, ['result' => 'Discount available for service!']);
        }
        $amount = [];
        $applied = false;
        foreach ($cart->items as $item) {
            $result = $this->voucherRepository
                ->isValid($data->voucher_code, $item->service->id, $item->partner->id, $data->location, $data->customer, $cart->price, $sales_channel);
            if ($result['is_valid']) {
                if ($this->voucherRepository->isOwnVoucher($data->customer, $result['voucher'])) {
                    $result = "Can't add your own voucher";
                    return api_response($request, $result, 403, ['result' => $result]);
                }
                $applied = true;
                $item->partner = $this->cartRepository->getPartnerPrice($item);
                if ($result['is_percentage']) {
                    $result['amount'] = ((float)$item->partner->prices * $item->quantity * $result['amount']) / 100;
                    if ($result['voucher']->cap != 0 && $result['amount'] > $result['voucher']->cap) {
                        $result['amount'] = $result['voucher']->cap;
                    }
                }
                $amount[] = (new DiscountRepository())->validateDiscountValue($item->partner->prices * $item->quantity, $result['amount']);
            }
        }
        if ($applied) {
            return api_response($request, max($amount), 200, ['amount' => max($amount)]);
        }
        return api_response($request, $result, 404, ['result' => max($result)]);
    }

    private function updateVoucherInPromoList(Customer $customer, $voucher, $order)
    {
        $rules = json_decode($voucher->rules);
        if (array_key_exists('nth_orders', $rules) && !array_key_exists('ignore_nth_orders_if_used', $rules)) {
            $nth_orders = $rules->nth_orders;
            if ($customer->orders->count() == max($nth_orders)) {
                $customer->promotions()->where('voucher_id', $order->voucher_id)->update(['is_valid' => 0]);
                return;
            }
        }
        if ($voucher->usage($customer->profile) == $voucher->max_order) {
            $customer->promotions()->where('voucher_id', $order->voucher_id)->update(['is_valid' => 0]);
            return;
        }
    }
}
