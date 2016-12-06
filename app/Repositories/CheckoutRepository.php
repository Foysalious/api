<?php

namespace App\Repositories;

use App\library\PortWallet;
use App\library\Sms;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\Job;
use App\Models\Order;
use App\Models\PartnerOrder;
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
        $cart = json_decode($order_info['cart']);
        //group cart_info by partners
        $cart_partner = collect($cart->items)->groupBy('partner.id');
        //Get all the unique partner id's
        $unique_partners = collect($cart->items)->unique('partner.id')->pluck('partner.id');
        $partner_price = [];
        //calculate total prices for each partner
        foreach ($cart_partner as $partners)
        {
            $price = 0;
            foreach ($partners as $partner)
            {
                $price += $partner->partner->prices;
            }
            $partner_price[$partner->partner->id] = $price;
        }
        $order = new Order();
        $order->customer_id = $order_info['customer_id'];
        $order->delivery_name = $order_info['name'];
        $order->delivery_mobile = $order_info['phone'];
        $order->delivery_address = $order_info['address'];
        if ($order->save())
        {
            foreach ($unique_partners as $partner)
            {
                $partner_order = new PartnerOrder();
                $partner_order->order_id = $order->id;
                $partner_order->partner_id = $partner;
                $partner_order->total_amount = $partner_price[$partner];
                $partner_order->payment_method = $payment_method;
                if ($partner_order->save())
                {
                    $partner_services = $cart_partner[$partner];
                    foreach ($partner_services as $service)
                    {
                        $job = new Job();
                        $job->partner_order_id = $partner_order->id;
                        $job->service_id = $service->service->id;
                        $job->service_name = $service->service->name;
                        $job->service_option = json_encode($service->serviceOptions);
                        $job->status = 'Open';
                        $job->save();
                    }
                }
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
        Mail::send('orders.order-verfication', ['customer' => $customer, 'order' => $order], function ($m) use ($customer)
        {
            $m->from('yourEmail@domain.com', 'Sheba.xyz');
            $m->to($customer->email)->subject('Order Verification');
        });
    }

    public function checkoutWithPortWallet($request, $customer)
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
        return $this->sendDataToPortwallet($cart->price, $service_names, $customer, $request, "/checkout/place-order-final");
    }

    public function spPaymentWithPortWallet($request, $customer)
    {
        $service_name = '';
        return $this->sendDataToPortwallet($request->price, $service_name, $customer, $request, "/checkout/sp-payment-final");
    }

    public function sendDataToPortwallet($amount, $product_name, $customer, $request, $redirect_url)
    {
        $data = array();
        $data['amount'] = $amount;
        $data['currency'] = "BDT";
        $data['product_name'] = $product_name;
        $data['product_description'] = "N/A";
        $data['name'] = !empty($customer->name) ? $customer->name : 'N/A';
        $data['email'] = isset($customer->email) ? $customer->email : 'N/A';
        $data['phone'] = isset($customer->mobile) ? $customer->mobile : 'N/A';
        $data['address'] = $request->get('address');
        $data['city'] = "N/A";
        $data['state'] = "N/A";
        $data['zipcode'] = "N/A";
        $data['country'] = "BD";
        $data['redirect_url'] = $this->appBaseUrl . $redirect_url;
        $data['ipn_url'] = $this->appBaseUrl . "ipn.php"; //IPN URL must be public URL which can be access remotely by portwallet system.
        $portwallet = $this->getPortWalletObject();
        $portwallet->setMode($this->appPaymentMode);
        $portwallet_response = $portwallet->generateInvoice($data);
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