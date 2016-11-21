<?php

namespace App\Http\Controllers;

use App\library\PortWallet;
use App\Models\Customer;
use App\Models\Job;
use App\Models\Order;
use App\Models\Service;
use App\Repositories\AuthRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Session;
use Cache;
use DB;
use Mail;

class CheckoutController extends Controller {

    private $authRepository;

    public function __construct()
    {
        return $this->authRepository = new AuthRepository();
    }

    public function placeOrder(Request $request)
    {
        return "yes";
    }

    public function placeOrderWithPayment(Request $request)
    {
        //Logged in user checkout
        if ($request->has('remember_token'))
        {
            //check for valid customer
            $customer = $this->authRepository->checkValidCustomer($request->input('remember_token'), $request->input('access_token'));
            //customer is valid so place the order
            if ($customer)
            {
                $cart = json_decode($request->input('cart'));
                $service_names = '';
                foreach ($cart->items as $cart_item)
                {
                    $service_names .= $cart_item->service->name . ',';
                }
                //gateway configuration

                /*
                // live
                $config = array();
                $config["appkey"] = "d64b3f0bfd0b7817f6dd9ce4c37eebf9";
                $config["secret"] = "b016471ad2fd795ab3d0443be639de42";
                $config["payment_mode"] = "live";
                $config["baseURL"] = "http://sheba.xyz";
                $payment_url = "https://payment.portwallet.com/payment/?invoice=";
                */


                // sandbox
                $config = array();
                $config["appkey"] = "e4d59d24b93dc2f9738c37d4109cce7a";
                $config["secret"] = "d45ba30ecc302e7fc179f1d5404d8ed1";
                $config["payment_mode"] = "sandbox";
                $config["baseURL"] = "http://192.168.1.109/sheba_new_api/public/v1";
                $payment_url = "https://payment-sandbox.portwallet.com/payment/?invoice=";

                $portwallet = new PortWallet($config["appkey"], $config["secret"]);
                $portwallet->setMode($config['payment_mode']);
                $data = array();
                $data['amount'] = $cart->price;
                $data['currency'] = "BDT";
                $data['product_name'] = $service_names;
                $data['product_description'] = "N/A";

                $data['name'] = 'Arnab';

                $data['email'] = 'abc@gmail.com';
                $data['phone'] = 'akja';
                $data['address'] = '\kajbkjb';
                $data['city'] = "N/A";
                $data['state'] = "N/A";
                $data['zipcode'] = "N/A";
                $data['country'] = "BD";

                $data['redirect_url'] = $config['baseURL'] . "/checkout/place-order-final";

                $data['ipn_url'] = $config['baseURL'] . "ipn.php"; //IPN URL must be public URL which can be access remotely by portwallet system.

                $portwallet_response = $portwallet->generateInvoice($data);
                if ($portwallet_response->status == 200)
                {
                    array_add($request, 'customer_id', $customer->id);
                    Cache::put('cart-with-payment-' . $portwallet_response->data->invoice_id, $request->all(), 30);
                    Cache::put('invoice-' . $portwallet_response->data->invoice_id, $portwallet_response->data->invoice_id, 30);
                    $url = $payment_url . $portwallet_response->data->invoice_id;
                    $response_data = array('code' => 200, 'gateway_url' => $url);
                }
                else
                {
                    $response_data = array('success' => false, 'message' => 'Payment Fail. Try Again');
                }
                return response()->json($response_data);
            }
            else
            {
                return response()->json(['code' => '404', 'msg' => 'Access denied!']);
            }
        }
        else
        {

        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function placeOrderFinal(Request $request)
    {
        $config = array();
        $config["appkey"] = "e4d59d24b93dc2f9738c37d4109cce7a";
        $config["secret"] = "d45ba30ecc302e7fc179f1d5404d8ed1";
        $data = array();
        $order_info = Cache::get('cart-with-payment-' . $request->get('invoice'));
        $cart = json_decode($order_info['cart']);
        $data["amount"] = $cart->price;
        $data["invoice"] = Cache::get('invoice-' . $request->get('invoice'));
        $data['currency'] = "BDT";

        $portwallet = new PortWallet($config["appkey"], $config["secret"]);
        $portwallet_response = $portwallet->ipnValidate($data);

        //check payment validity
        if ($portwallet_response->status == 200 && $portwallet_response->data->status == "ACCEPTED")
        {
            $order = new Order();
            $order->customer_id = $order_info['customer_id'];
            $order->delivery_name = isset($order_info['name']) ? $order_info['name'] : '';
            $order->delivery_mobile = $order_info['phone'];
            $order->delivery_address = $order_info['address'];
            $order->created_by = 'Customer';
            DB::transaction(function () use ($order, $cart)
            {
                $order->save();
                foreach ($cart->items as $cart_item)
                {
                    $job = new Job();
                    $job->service_id = $cart_item->service->id;
                    $job->service_name = $cart_item->service->name;
                    $job->partner_id = $cart_item->partner->id;
                    $job->payment_method = 'online';
                    $order->jobs()->save($job);
                }
            });

            //send message
//            $phone = Session::get('login_mobile');
//            $message = "Thanks for placing order at www.sheba.xyz. Order ID No : " . $order_id;
//            Sms::send_single_message($phone, $message);
//            $response_data = array('success' => true, 'order_id' => $order_id);

//// mail send to customer and sheba
//            $orders_info = DB::table('orders')
//                    ->select('orders.*', 'member_login_infoes.*', 'member_login_infoes.id as member_login_id', 'members.name')
//                    ->leftJoin('member_login_infoes', 'member_login_infoes.member_id', '=', 'orders.customer_id')
//                    ->leftJoin('members', 'members.id', '=', 'orders.customer_id')
//                    ->where('orders.order_id', $order_id)
//                    ->first();
//
//
//            $job_info = DB::table('orderdetails')
//                    ->select('*')
//                    ->where('order_id', $order_id)
//                    ->get();
//
//
            //send order info to customer  by mail
            $customer = Customer::find($order->customer_id);
            if ($customer->email != '')
            {
                Mail::send('orders.order-verfication', ['customer' => $customer, 'order' => $order, 'jobs' => $order->jobs], function ($m) use ($customer)
                {
                    $m->from('yourEmail@domain.com', 'Sheba.xyz');
                    $m->to($customer->email)->subject('Order Verification');

                });
//                Mail::send('front_end.mail.send_member_order_info', ['orders_info' => $orders_info, 'job_info' => $job_info], function ($message) use ($orders_info)
//                {
//                    // $m->from('alatif@sheba.xyz', 'Sheba Ecommerce');
//                    $message->to($orders_info->email, $orders_info->name)->subject('Order Info');
//                    $message->getSwiftMessage();
//                });
            }
            //send Order info to sheba by mail
//            Mail::send('front_end.mail.send_member_order_info', ['orders_info' => $orders_info, 'job_info' => $job_info], function ($message) use ($orders_info)
//            {
//                // $m->from('alatif@sheba.xyz', 'Sheba Ecommerce');
//                $message->to('info@sheba.xyz', 'Sheba')->subject('New Order');
//                $message->getSwiftMessage();
//            });


        }
        else
        {
            return redirect('');
        }
        return redirect('http://localhost:8080');

    }
}
