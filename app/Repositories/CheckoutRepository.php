<?php

namespace App\Repositories;

use App\Jobs\SendOrderConfirmationEmail;
use App\Library\PortWallet;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\CustomOrder;
use App\Models\Job;
use App\Models\Order;
use App\Models\PartnerOrder;
use App\Models\PartnerOrderPayment;
use App\Models\PartnerServiceDiscount;
use App\Models\Service;
use App\Models\User;
use Cache;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\DB;


class CheckoutRepository
{
    use DispatchesJobs;
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
     * Portwallet payment url where to redirect after a successful connection response
     * @var mixed
     */
    private $appPaymentUrl;

    private $voucherRepository;
    private $discountRepository;
    private $created_by;
    private $created_by_name;

    public function __construct()
    {
        $this->created_by = 0;
        $this->created_by_name = 'Customer';
        $this->appKey = config('portwallet.app_key');
        $this->appSecret = config('portwallet.app_secret');
        $this->appPaymentMode = config('portwallet.app_payment_mode');
        $this->appBaseUrl = config('portwallet.app_base_url');
        $this->appPaymentUrl = config('portwallet.app_payment_url');
        $this->voucherRepository = new VoucherRepository();
        $this->discountRepository = new DiscountRepository();
    }

    public function storeDataInDB($order_info, $payment_method)
    {
        $order = new Order();
        try {
            DB::transaction(function () use ($order_info, $payment_method, $order) {
                $this->calculateAuthor($order_info);
                $cart = json_decode($order_info['cart']);

                $order = $this->createOrder($order, $order_info);
                $order->delivery_address = $this->getDeliveryAddress($order_info);
                $order->update();

                //For custom order
                if (isset($cart->custom_order_id)) {
                    $this->updateCustomOrder($order, $cart->custom_order_id);
                }
                (new CustomerRepository())->updateCustomerNameIfEmptyWhenPlacingOrder($order_info);

                $cart_partner = collect($cart->items)->groupBy('partner.id');
                //Get all the unique partner id's
                $unique_partners = collect($cart->items)->unique('partner.id')->pluck('partner.id');
                $voucher = 0;

                foreach ($unique_partners as $partner) {
                    $partner_order = $this->createPartnerOrder($order, $partner, $payment_method);
                    if ($payment_method == 'online') {
                        $partner_order_price = $this->calculatePartnerOrderPrice($cart_partner);
                        $partner_order->sheba_collection = $partner_order_price[$partner];
                        $partner_order->update();
                        $this->createPartnerOrderPayment($partner_order);
                    }
                    $partner_services = $cart_partner[$partner];
                    foreach ($partner_services as $service) {
                        $job = new Job();
                        $job->partner_order_id = $partner_order->id;
                        $job->service_id = $service->service->id;
                        $job->service_name = $service->service->name;
                        $job->preferred_time = $service->time;
                        $job->job_additional_info = $service->additional_info;
                        $job->service_quantity = $service->quantity;
                        $job->crm_id = isset($service->crm_id) ? $service->crm_id : '';
                        $job->department_id = isset($service->department_id) ? $service->department_id : '';
                        $job->service_unit_price = (float)$service->partner->prices;
                        $job = $this->calculateDiscountOrVoucher($order, $partner_order, $job, $cart, $service);
                        $job->job_name = isset($service->job_name) ? $service->job_name : '';
                        $job->schedule_date = $this->calculateScheduleDate($service->date);
                        //For custom order
                        if (isset($cart->custom_order_id)) {
                            $job->service_variables = json_encode($service->serviceOptions);
                            $job->service_variable_type = 'Custom';
                        } else {
                            $job->service_option = json_encode($service->serviceOptions);
                            //shafiq
                            if (empty($service->service->variable_type) || empty($service->service->variables)) {
                                $service_details = Service::find($service->service->id);
                                $job->service_variables = $service_variables = $service_details->variables;
                                $job->service_variable_type = $service_variable_type = $service_details->variable_type;
                            } else {
                                $job->service_variable_type = $service_variable_type = $service->service->variable_type;
                                $job->service_variables = $service_variables = json_encode($service->service->variables);
                            }

                            $service_variables = json_decode($service_variables, 1);
                            $service_option = $service->serviceOptions;

                            $job_options = [];
                            if ($service_variable_type == 'Options') {
                                foreach ($service_variables['options'] as $key => $option) {
                                    array_push($job_options, [
                                        'question' => $option['question'],
                                        'answer' => explode(',', $option['answers'])[$service_option[$key]]
                                    ]);
                                }
                            }
                            $job->service_variables = json_encode($job_options);
                        }
                        $job = $this->getAuthor($job);
                        $job->save();
                        $partner = $partner_order->partner_id;
                        $service = Service::find($job->service_id);
                        $job->commission_rate = $service->commission($partner);
                        $job->vat = 0;
                        $job->update();
                    }
                }
            });
        } catch (QueryException $e) {
            return false;
        }
        return $order;
    }

    private function calculateAuthor($order_info)
    {
        if (isset($order_info['created_by'])) {
            $user = User::find($order_info['created_by']);
            $this->created_by = $user->id;
            $this->created_by_name = $user->name;
        }
    }

    private function getAuthor($object)
    {
        $object->created_by = $this->created_by;
        $object->created_by_name = $this->created_by_name;
        return $object;
    }

    private function createOrder($order, $order_info)
    {
        $order->customer_id = $order_info['customer_id'];
        $order->location_id = $order_info['location_id'];
        $order->delivery_name = $order_info['name'];
        $order->delivery_mobile = $order_info['phone'];
        $order->sales_channel = isset($order_info['sales_channel']) ? $order_info['sales_channel'] : 'Web';
        $order = $this->getAuthor($order);
        $order->save();
        return $order;
    }

    private function createPartnerOrder($order, $partner, $payment_method)
    {
        $partner_order = new PartnerOrder();
        $partner_order->order_id = $order->id;
        $partner_order->partner_id = $partner;
        $partner_order->payment_method = $payment_method;
        $partner_order = $this->getAuthor($partner_order);
        $partner_order->save();
        return $partner_order;
    }

    private function updateCustomOrder($order, $custom_order_id)
    {
        $custom_order = CustomOrder::find($custom_order_id);
        $custom_order->order_id = $order->id;
        $custom_order->status = 'Converted To Order';
        $custom_order->update();
    }

    private function calculatePartnerOrderPrice($cart_partner)
    {
        $partner_order_price = [];
        //calculate total prices for each partner
        foreach ($cart_partner as $partner_services) {
            $price = 0;
            foreach ($partner_services as $partner_service) {
                $price += ($partner_service->partner->prices * $partner_service->quantity);
            }
            $partner_order_price[$partner_service->partner->id] = $price;
        }
        return $partner_order_price;
    }

    private function getDeliveryAddress($order_info)
    {
        if ($order_info['address_id'] != '') {
            $deliver_address = CustomerDeliveryAddress::find($order_info['address_id']);
            return $deliver_address->address;
        } elseif ($order_info['address'] != '') {
            $deliver_address = new CustomerDeliveryAddress();
            $deliver_address->address = $order_info['address'];
            $deliver_address->customer_id = $order_info['customer_id'];
            $deliver_address = $this->getAuthor($deliver_address);
            $deliver_address->save();
            return $order_info['address'];
        }
    }

    private function createPartnerOrderPayment($partner_order)
    {
        $partner_order_payment = $this->getPartnerOrderPayment($partner_order);
        $partner_order_payment->amount = $partner_order->sheba_collection;
        $partner_order_payment->log = 'advanced payment';
        $partner_order_payment = $this->getAuthor($partner_order_payment);
        $partner_order_payment->save();
    }

    private function calculateDiscountOrVoucher($order, $partner_order, $job, $cart, $service)
    {
        if (isset($service->partner->discount_id)) {
            $discount = PartnerServiceDiscount::find($service->partner->discount_id);
            $job->discount = $this->discountRepository->getDiscountAmount($discount->is_amount_percentage, $service->partner->prices, $discount->amount) * $job->service_quantity;
            $job->sheba_contribution = $discount->sheba_contribution;
            $job->partner_contribution = $discount->partner_contribution;
        } elseif (isset($cart->voucher) && $order->voucher_id == null) {
            $result = $this->voucherRepository
                ->isValid($cart->voucher, $service->service->id, $partner_order->partner_id, $order->location_id, $order->delivery_mobile, $cart->price, $order->sales_channel);
            if ($result['is_valid']) {
                $job->discount = $this->discountRepository->getDiscountAmount($result['is_percentage'], $service->partner->prices, $result['voucher']['amount']) * $job->service_quantity;
                $job->sheba_contribution = $result['voucher']['sheba_contribution'];
                $job->partner_contribution = $result['voucher']['partner_contribution'];
                $order->voucher_id = $result['id'];
                $order->update();
            }
        };
        return $job;
    }

    private function calculateScheduleDate($date)
    {
        if (is_object($date)) {
            return Carbon::parse($date->time)->format('Y-m-d');
        } else {
            return Carbon::parse($date)->format('Y-m-d');
        }
    }

    public function clearSpPayment($payment_info)
    {
        $partner_order_id = array_unique($payment_info['partner_order_id']);
        $partner = [];
        for ($i = 0; $i < count($partner_order_id); $i++) {
            $partner_order = PartnerOrder::find($partner_order_id[$i]);
            $partner_order->calculate();
            //to send data in email
            array_push($partner, array("partner_order_id" => $partner_order->id, "due" => $partner_order->due));
            $partner_order_payment = $this->getPartnerOrderPayment($partner_order);
            $partner_order_payment->amount = $partner_order->due;
            $partner_order_payment->log = 'Due paid';
            $partner_order_payment->save();
            $partner_order->payment_method = 'online';
            $partner_order->sheba_collection = $partner_order->due;
            $partner_order->update();
        }
        $this->sendSpPaymentClearMail($partner);
    }

    public function sendSpPaymentClearMail($partner)
    {

//        Mail::send('orders.order-verfication', ['customer' => $customer, 'order' => $order], function ($m) use ($customer)
//        {
//            $m->from('yourEmail@domain.com', 'Sheba.xyz');
//            $m->to($customer->email)->subject('Order Verification');
//        });
    }

    public function sendOrderConfirmationMail($order, $customer)
    {
        Mail::send('orders.order-verfication', ['customer' => $customer, 'order' => $order], function ($m) use ($customer) {
            $m->from('yourEmail@domain.com', 'Sheba.xyz');
            $m->to($customer->email)->subject('Order Verification');
        });
    }

    public function checkoutWithPortWallet($request, $customer)
    {
        $cart = json_decode($request->input('cart'));
        $service_names = '';
        //get the service names
        foreach ($cart->items as $cart_item) {
            $service_names .= $cart_item->service->name . ',';
        }
        // remove comma from the end of service name
        $service_names = rtrim($service_names, ",");
        return $this->sendDataToPortwallet($cart->price, $service_names, $customer, $request, "/checkout/place-order-final");
    }

    public function spPaymentWithPortWallet($request, $customer)
    {
        $service_name = $request->input('service_name');
        $partner_order_id = $request->input('partner_order_id');
        $product_name = '';
        for ($i = 0; $i < count($service_name); $i++) {
            $product_name .= $service_name[$i] . ',';
        }
        $product_name = rtrim($product_name, ",");
        $partner_order = PartnerOrder::find($partner_order_id[0]);
        array_add($request, 'address', $partner_order->order->delivery_address);
        array_add($request, 'phone', $partner_order->order->delivery_mobile);
        return $this->sendDataToPortwallet($request->input('price'), $product_name, $customer, $request, "/checkout/sp-payment-final");
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
        $data['phone'] = $request->input('phone');
        if ($request->input('address') != '') {
            $data['address'] = $request->input('address');
        } else {
            $customer_address = CustomerDeliveryAddress::find($request->input('address_id'));
            $data['address'] = $customer_address->address;
        }
        $data['city'] = "city";
        $data['state'] = "state";
        $data['zipcode'] = "zipcode";
        $data['country'] = "BD";
        $data['redirect_url'] = $this->appBaseUrl . $redirect_url;
        $data['ipn_url'] = $this->appBaseUrl . "/ipn.php"; //IPN URL must be public URL which can be access remotely by portwallet system.
        $portwallet = $this->getPortWalletObject();
        $portwallet_response = $portwallet->generateInvoice($data);
        if ($portwallet_response->status == 200) {
            array_add($request, 'customer_id', $customer->id);
            Cache::forever('portwallet-payment-' . $portwallet_response->data->invoice_id, $request->all());
            Cache::forever('invoice-' . $portwallet_response->data->invoice_id, $portwallet_response->data->invoice_id);
            $url = $this->appPaymentUrl . $portwallet_response->data->invoice_id;
            return (['code' => 200, 'gateway_url' => $url]);
        } else {
            return (['code' => 500, 'msg' => 'Payment Gateway connection failed']);
        }
    }

    public function getPortWalletObject()
    {
        $portwallet = new PortWallet($this->appKey, $this->appSecret);
        $portwallet->setMode($this->appPaymentMode);
        return $portwallet;
    }

    private function getPartnerOrderPayment($partner_order)
    {
        $partner_order_payment = new PartnerOrderPayment();
        $partner_order_payment->partner_order_id = $partner_order->id;
        $partner_order_payment->transaction_type = 'Credit';
        $partner_order_payment->method = 'online';
        return $partner_order_payment;
    }

    public function sendConfirmation($customer, $order)
    {
        $customer = ($customer instanceof Customer) ? $customer : Customer::find($customer);
        //send order info to customer  by mail
        (new SmsHandler('order-created'))->send($customer->mobile, [
            'order_code' => $order->code()
        ]);
        if ($customer->email != '') {
            $this->dispatch(new SendOrderConfirmationEmail($customer, $order));
        }
    }
}