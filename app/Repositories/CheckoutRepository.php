<?php

namespace App\Repositories;

use App\library\PortWallet;
use App\library\Sms;
use App\Models\Job;
use App\Models\Order;
use DB;
use Illuminate\Http\Request;
use Mail;
use Cache;

class CheckoutRepository {
    /**
     * Portwallet app key
     * @var mixed
     */
    private $appKey;
    /**
     * Portwallet app secret@var mixed
     */
    private $appSecret;
    /**
     * Portwallet payment mode
     * @var mixed
     */
    private $appPaymentMode;
    /**
     * Base url where to redirect after a successful payment completition through portwallet
     * @var mixed
     */
    private $appBaseUrl;
    /**
     * Portwallet payment url where to redirect after a successful connection reponse
     * @var mixed
     */
    private $appPaymentUrl;

    public function __construct()
    {
        $this->appKey = config('portwallet.app_key');
        $this->appSecret = config('portwallet.app_secret');
        $this->appPaymentMode = config('portwallet.app_payment_mode');
        $this->appBaseUrl = config('portwallet.app_base_url');
        $this->appPaymentUrl = config('portwallet.app_payment_url');
    }

    public function storeDataInDB($order_info, $payment_method)
    {
        $order = new Order();
        $cart = json_decode($order_info['cart']);
        $order->customer_id = $order_info['customer_id'];
        $order->delivery_name = isset($order_info['name']) ? $order_info['name'] : '';
        $order->delivery_mobile = $order_info['phone'];
        $order->delivery_address = $order_info['address'];
        if ($order->save())
        {
            foreach ($cart->items as $cart_item)
            {
                $job = new Job();
                $job->service_id = $cart_item->service->id;
                $job->service_name = $cart_item->service->name;
                $job->partner_id = $cart_item->partner->id;
                $job->payment_method = $payment_method;
                $order->jobs()->save($job);
            }
        }
        //send order info to customer  by mail
        $customer = Customer::find($order->customer_id);
        if ($customer->email != '')
        {
            $this->sendOrderConfirmationMail($order, $customer);
        }
        if ($customer->mobile != '')
        {
            $message = "Thanks for placing order at www.sheba.xyz. Order ID No : " . $order->id;
            Sms::send_single_message($customer->mobile, $message);
        }
        return $order;
    }

    public function sendOrderConfirmationMail($order, $customer)
    {
        Mail::send('orders.order-verfication', ['customer' => $customer, 'order' => $order, 'jobs' => $order->jobs], function ($m) use ($customer)
        {
            $m->from('yourEmail@domain.com', 'Sheba.xyz');
            $m->to($customer->email)->subject('Order Verification');
        });
    }

    public function connectWithPortWallet(Request $request, $customer)
    {
        $cart = json_decode($request->input('cart'));
        $service_names = '';
        //get the service names
        foreach ($cart->items as $cart_item)
        {
            $service_names .= $cart_item->service->name . ',';
        }
        // remove comma from the end of service name
        $service_names = rtrim($service_names, ",");

        $portwallet = new PortWallet($this->appKey, $this->appSecret);
        $portwallet->setMode($this->appPaymentMode);
        $data = array();
        $data['amount'] = $cart->price;
        $data['currency'] = "BDT";
        $data['product_name'] = $service_names;
        $data['product_description'] = "N/A";
        $data['name'] = !empty($customer->name) ? $customer->name : 'N/A';
        $data['email'] = isset($customer->email) ? $customer->email : 'N/A';
        $data['phone'] = isset($customer->mobile) ? $customer->mobile : 'N/A';
        $data['address'] = $request->get('address');
        $data['city'] = "N/A";
        $data['state'] = "N/A";
        $data['zipcode'] = "N/A";
        $data['country'] = "BD";
        $data['redirect_url'] = $this->appBaseUrl . "/checkout/place-order-final";
        $data['ipn_url'] = $this->appBaseUrl . "ipn.php"; //IPN URL must be public URL which can be access remotely by portwallet system.

        $portwallet_response = $portwallet->generateInvoice($data);
        //successfull connection established
        if ($portwallet_response->status == 200)
        {
            array_add($request, 'customer_id', $customer->id);
            Cache::put('cart-with-payment-' . $portwallet_response->data->invoice_id, $request->all(), 30);
            Cache::put('invoice-' . $portwallet_response->data->invoice_id, $portwallet_response->data->invoice_id, 30);
            $url = $this->appPaymentUrl . $portwallet_response->data->invoice_id;
            return (['code' => 200, 'gateway_url' => $url]);
        }
        else
        {
            return (['code' => 500, 'msg' => 'Payment Gateway connection failed']);
        }
    }

    public function getPortWalletObject()
    {
        return new PortWallet($this->appKey, $this->appSecret);
    }

}