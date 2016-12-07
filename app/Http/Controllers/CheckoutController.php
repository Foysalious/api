<?php

namespace App\Http\Controllers;

use App\library\PortWallet;
use App\library\Sms;
use App\Models\Customer;
use App\Repositories\AuthRepository;
use App\Repositories\CheckoutRepository;
use App\Repositories\CustomerRepository;
use Illuminate\Http\Request;
use Session;
use Cache;
use DB;
use Mail;

class CheckoutController extends Controller {
    private $authRepository;
    private $checkoutRepository;

    private $fbKit;
    private $customer;

    public function __construct()
    {
        $this->authRepository = new AuthRepository();
        $this->checkoutRepository = new CheckoutRepository();
        $this->fbKit = new FacebookAccountKit();
        $this->customer = new CustomerRepository();
    }

    public function placeOrder(Request $request)
    {
        //Logged in customer chechkout
        if ($request->has('remember_token'))
        {
            //check for valid customer
            $customer = $this->authRepository->checkValidCustomer($request->input('remember_token'), $request->input('access_token'));
            //If a customer is found
            if ($customer)
            {
                array_add($request, 'customer_id', $customer->id);
                //store order details for customer
                $order = $this->checkoutRepository->storeDataInDB($request->all(), 'cash-on-delivery');
                if (!empty($order))
                {
                    //send order info to customer  by mail
                    $customer = Customer::find($order->customer_id);
                    if ($customer->email != '')
                    {
                        $this->checkoutRepository->sendOrderConfirmationMail($order, $customer);
                    }
                    if ($customer->mobile != '')
                    {
                        $message = "Thanks for placing order at www.sheba.xyz. Order ID No : " . $order->id;
                        Sms::send_single_message($customer->mobile, $message);
                    }
                    return response()->json(['code' => 200, 'msg' => 'Order placed successfully!']);
                }
            }
            //customer credentials failed
            else
            {
                return response()->json(['code' => 404, 'msg' => 'Access denied!']);
            }
        }
    }

    /**
     * Place with order with payment
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeOrderWithPayment(Request $request)
    {
        //Logged in user checkout
        if ($request->has('remember_token'))
        {
            //check for valid customer
            $customer = $this->authRepository->checkValidCustomer($request->input('remember_token'), $request->input('access_token'));
            //If customer is valid place the order
            if ($customer)
            {
                $connectionResponse = $this->checkoutRepository->checkoutWithPortWallet($request, $customer);
                return response()->json($connectionResponse);
            }
            //customer credentials failed
            else
            {
                return response()->json(['code' => 404, 'msg' => 'Access denied!']);
            }
        }
        elseif ($request->has('code'))
        {
            //Authenticate the code with facebook kit
            $code_data = $this->fbKit->authenticateKit($request->input('code'));
            //return error if customer already exists
            if ($this->customer->ifExist($code_data['mobile'], 'mobile'))
            {
                return response()->json(['message' => 'number already exists', 'code' => 409]);
            }
            //register the customer with verified mobile
            $customer = $this->customer->registerMobile($code_data['mobile']);

            $connectionResponse = $this->checkoutRepository->checkoutWithPortWallet($request, $customer);
            return response()->json($connectionResponse);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function placeOrderFinal(Request $request)
    {
        $portwallet = $this->checkoutRepository->getPortWalletObject();
        $order_info = Cache::get('portwallet-payment-' . $request->get('invoice'));
        $cart = json_decode($order_info['cart']);
        $data = array();
        $data["amount"] = $cart->price;
        $data["invoice"] = Cache::get('invoice-' . $request->get('invoice'));
        $data['currency'] = "BDT";

        $portwallet_response = $portwallet->ipnValidate($data);

        //check payment validity
        if ($portwallet_response->status == 200 && $portwallet_response->data->status == "ACCEPTED")
        {
            $order = $this->checkoutRepository->storeDataInDB($order_info, 'online');
            if (!empty($order))
            {
                Cache::forget('invoice-' . $request->get('invoice'));
                Cache::forget('portwallet-payment-' . $request->get('invoice'));
                return redirect('http://localhost:8080');
            }
        }
        else
        {
            return;
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
        $payment_info = Cache::get('portwallet-payment-' . $request->get('invoice'));
        $data = array();
        $data["amount"] = $payment_info['price'];
        $data["invoice"] = Cache::get('invoice-' . $request->get('invoice'));
        $data['currency'] = "BDT";
        $portwallet_response = $portwallet->ipnValidate($data);
        //check payment validity
        if ($portwallet_response->status == 200 && $portwallet_response->data->status == "ACCEPTED")
        {
            $this->checkoutRepository->clearSpPayment($payment_info);
            Cache::forget('invoice-' . $request->get('invoice'));
            Cache::forget('portwallet-payment-' . $request->get('invoice'));
            return redirect('http://localhost:8080');
        }
        else
        {
            return;
        }
    }
}
