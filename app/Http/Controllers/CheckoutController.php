<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use App\Repositories\AuthRepository;
use App\Repositories\CheckoutRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\VoucherRepository;
use Illuminate\Http\Request;
use Session;
use Cache;
use DB;
use Mail;
use Redis;

class CheckoutController extends Controller
{
    private $authRepository;
    private $checkoutRepository;
    private $voucherRepository;
    private $fbKit;
    private $customer;

    public function __construct()
    {
        $this->authRepository = new AuthRepository();
        $this->checkoutRepository = new CheckoutRepository();
        $this->fbKit = new FacebookAccountKit();
        $this->customer = new CustomerRepository();
        $this->voucherRepository = new VoucherRepository();
    }

    public function placeOrder(Request $request, $customer)
    {
        array_add($request, 'customer_id', $customer);
        //store order details for customer
        $order = $this->checkoutRepository->storeDataInDB($request->all(), 'cash-on-delivery');
        if ($order) {
            new NotificationRepository($order);
            $this->checkoutRepository->sendConfirmation($customer, $order);
            return response()->json(['code' => 200, 'msg' => 'Order placed successfully!']);
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
        $customer = Customer::find($customer);
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
        $data["amount"] = $cart->price;
        $data["invoice"] = Cache::get('invoice-' . $request->input('invoice'));
        $data['currency'] = "BDT";
        $portwallet_response = $portwallet->ipnValidate($data);
        //check payment validity
        if ($portwallet_response->status == 200 && $portwallet_response->data->status == "ACCEPTED") {
            $order = $this->checkoutRepository->storeDataInDB($order_info, 'online');
            if ($order) {
                $this->checkoutRepository->sendConfirmation($order_info['customer_id'], $order);
                Cache::forget('invoice-' . $request->input('invoice'));
                Cache::forget('portwallet-payment-' . $request->input('invoice'));
                $s_id = str_random(10);
                Redis::set($s_id, 'online');
                Redis::expire($s_id, 500);
                return redirect(env('SHEBA_FRONT_END_URL') . '/order-list?s_token=' . $s_id);
            }
        } else {
            return "Something went wrong";
        }
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
            $this->checkoutRepository->clearSpPayment($payment_info);
            Cache::forget('invoice-' . $request->input('invoice'));
            Cache::forget('portwallet-payment-' . $request->input('invoice'));
            return redirect(env('SHEBA_FRONT_END_URL') . '/order-list');
        } else {
            return "Something went wrong";
        }
    }

    /**
     * Check if voucher is valid
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkForValidity(Request $request)
    {
        $sales_channel = ($request->has('sales_channel')) ? $request->sales_channel : "Web";
        $cart = json_decode($request->cart);
        foreach ($cart->items as $item) {
            $result = $this->voucherRepository
                ->isValid($request->voucher_code, $item->service->id, $item->partner->id, $request->location, $request->mobile, $cart->price, $sales_channel);
            if ($result['is_valid']) {
                return response()->json(['code' => 200, 'amount' => $result['amount']]);
            }
        }
        return response()->json(['code' => 404, 'msg' => 'invalid']);
    }
}
