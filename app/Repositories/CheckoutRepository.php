<?php namespace App\Repositories;

use App\Library\PortWallet;
use App\Models\Affiliation;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\CustomOrder;
use Sheba\Dal\InfoCall\InfoCall;
use App\Models\Job;
use App\Models\Order;
use App\Models\PartnerOrder;
use App\Sheba\Sms\BusinessType;
use App\Sheba\Sms\FeatureType;
use Sheba\Dal\PartnerOrderPayment\PartnerOrderPayment;
use App\Models\PartnerServiceDiscount;
use Sheba\Dal\Service\Service;
use Cache;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\DB;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

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
    private $cartRepository;
    private $created_by;
    private $created_by_name;
    private $voucherApplied = false;
    private $discountApplied = false;

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
        $this->cartRepository = new CartRepository();
    }

    public function storeDataInDB($order_info, $payment_method)
    {
        $cart = json_decode($order_info['cart']);
        $job_discount = [];
        $job_discount['discount'] = 0;
        $cart_partner = collect($cart->items)->groupBy('partner.id');
        //Get all the unique partner id's
        $unique_partners = collect($cart->items)->unique('partner.id')->pluck('partner.id');

        $i = 0;
        $j = 0;
        $loop_id = [];
        $loop_id['i'] = null;
        $loop_id['j'] = null;
        $loop_id['discount'] = 0;
        foreach ($unique_partners as $partner) {
            $i++;
            $partner_services = $cart_partner[$partner];
            foreach ($partner_services as $service) {
                $j++;
                $job_discount = $this->calculateVoucher($cart, $service, $order_info, $job_discount);
                if ($this->voucherApplied && $loop_id['discount'] < $job_discount['discount']) {
                    $loop_id = array(
                        'i' => $i,
                        'j' => $j,
                        'discount' => $job_discount['discount'],
                    );
                }
            }
        }
        $order = new Order();
        try {
            DB::transaction(function () use ($order_info, $cart, $payment_method, $order, $job_discount, $loop_id) {
                $this->calculateAuthor($order_info);

                $order = $this->createOrder($order, $order_info);
                $order->delivery_address = $this->getDeliveryAddress($order_info);
                //For custom order
                if (isset($cart->custom_order_id)) {
                    $order->custom_order_id = $cart->custom_order_id;
                    $this->updateCustomOrder($order, $cart->custom_order_id);
                }
                $order->update();

                (new CustomerRepository())->updateCustomerNameIfEmptyWhenPlacingOrder($order_info);

                $cart_partner = collect($cart->items)->groupBy('partner.id');
                //Get all the unique partner id's
                $unique_partners = collect($cart->items)->unique('partner.id')->pluck('partner.id');
                $voucher = 0;

                $i = 0;
                $j = 0;
                foreach ($unique_partners as $partner) {
                    $i++;
                    $partner_order = $this->createPartnerOrder($order, $partner, $payment_method);
                    if ($payment_method == 'online') {
                        $partner_order_price = $this->calculatePartnerOrderPrice($cart_partner);
                        $partner_order->sheba_collection = $partner_order_price[$partner];
                    }
                    $partner_services = $cart_partner[$partner];
                    foreach ($partner_services as $service) {
                        $j++;
                        $job = new Job();
                        $job->partner_order_id = $partner_order->id;
                        $job->service_id = $service->service->id;
                        $job->service_name = $service->service->name;
                        $job->preferred_time = $service->time;
                        $job->job_additional_info = $service->additional_info;
                        $job->service_quantity = $service->quantity;
                        $job->crm_id = isset($service->crm_id) ? $service->crm_id : null; //REMOVE
                        $job->resource_id = isset($service->resource_id) ? $service->resource_id : null;
                        $job->status = isset($service->resource_id) ? constants('JOB_STATUSES')['Accepted'] : constants('JOB_STATUSES')['Pending'];
                        $job->department_id = isset($service->department_id) ? $service->department_id : '';
                        $job->service_unit_price = (float)$service->partner->prices;
                        if (isset($service->partner->discount_id)) {
                            $discount = PartnerServiceDiscount::find($service->partner->discount_id);
                            $job->discount = $this->discountRepository
                                ->getServiceDiscountAmount($discount, $service->partner->prices, $service->quantity);
                            $job->sheba_contribution = $discount->sheba_contribution;
                            $job->partner_contribution = $discount->partner_contribution;
                            $job->discount_percentage = $discount->is_amount_percentage ? $discount->amount : null;
                            $this->discountApplied = true;
                        } elseif ($this->voucherApplied && $loop_id['i'] == $i && $loop_id['j'] == $j) {
                            $job->discount = $loop_id['discount'];
                            $job->sheba_contribution = $job_discount['sheba_contribution'];
                            $job->partner_contribution = $job_discount['partner_contribution'];
                            $this->voucherApplied = false;
                            $order->voucher_id = $job_discount['voucher_id'];
                            $order->update();
                        }
                        if ($payment_method == 'online' && isset($job->discount)) {
                            $partner_order->sheba_collection = $partner_order->sheba_collection - $job->discount;
                        }
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
//                                $job->service_variables = $service_variables = json_encode($service->service->variables);
                                $job->service_variables = $service_variables = $service->service->variables;
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
                    if ($payment_method == 'online') {
                        $partner_order->sheba_collection = floor($partner_order->sheba_collection);
                        $partner_order->update();
                        $this->createPartnerOrderPayment($partner_order, $order_info['portwallet_response']);
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
//            $user = User::find($order_info['created_by']);
//            $this->created_by = $user->id;
//            $this->created_by_name = $user->name;
            $this->created_by = $order_info['created_by'];
        }
        if (isset($order_info['created_by_name'])) {
            $this->created_by_name = $order_info['created_by_name'];
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
        $order->info_call_id = $this->_setInfoCallId($order_info);
        $order->affiliation_id = $this->_setAffiliationId($order_info);
        $order->delivery_name = $order_info['name'];
        $order->delivery_mobile = formatMobile($order_info['phone']);
        $order->sales_channel = isset($order_info['sales_channel']) ? $order_info['sales_channel'] : 'Web';
        if (isset($order_info['pap_visitor_id'])) {
            $order->pap_visitor_id = $order_info['pap_visitor_id'];
        }
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
//        $custom_order->order_id = $order->id;
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

    private function createPartnerOrderPayment($partner_order, $portwallet_response)
    {
        $partner_order_payment = $this->getPartnerOrderPayment($partner_order);
        $partner_order_payment->amount = floor($partner_order->sheba_collection);
        $partner_order_payment->log = 'advanced payment';
        $partner_order_payment->collected_by = 'Sheba';
        $partner_order_payment->transaction_detail = json_encode($portwallet_response);
        $partner_order_payment = $this->getAuthor($partner_order_payment);
        $partner_order_payment->save();
    }

    private function calculateVoucher($cart, $service, $order_info, $job)
    {
        if (isset($cart->voucher)) {
            $result = $this->voucherRepository
                ->isValid($cart->voucher, $service->service->id, $service->partner->id, $order_info['location_id'], (int)$order_info['customer_id'], $cart->price, isset($order_info['sales_channel']) ? $order_info['sales_channel'] : 'Web');
            if ($result['is_valid']) {
                $job['discount'] = $this->discountRepository
                    ->getDiscountAmount($result, $service->partner->prices, $service->quantity);
                $job['sheba_contribution'] = $result['voucher']['sheba_contribution'];
                $job['partner_contribution'] = $result['voucher']['partner_contribution'];
                $job['voucher_id'] = $result['id'];
                $this->voucherApplied = true;
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

    public function clearSpPayment($payment_info, $portwallet_response)
    {
        $partner_order_id = array_unique($payment_info['partner_order_id']);
        try {
            $client = new Client();
            $res = $client->request('POST', config('sheba.admin_url') . '/api/partner-order/' . $partner_order_id[0] . '/collect',
                [
                    'form_params' => [
                        'customer_id' => $payment_info['customer_id'],
                        'remember_token' => $payment_info['remember_token'],
                        'sheba_collection' => (double)$payment_info['price'],
                        'payment_method' => 'Online',
                        'transaction_detail' => json_encode($portwallet_response)
                    ]
                ]);
            return json_decode($res->getBody());
        } catch (RequestException $e) {
            return false;
        }
    }

    public function sendOrderConfirmationMail($order, $customer)
    {
        Mail::send('orders.order-verfication', ['customer' => $customer, 'order' => $order], function ($m) use ($customer) {
            $m->to($customer->email)->subject('Order Verification');
        });
    }

    public function checkoutWithPortWallet($request, $customer)
    {
        $cart = json_decode($request->cart);
        $cart->items = $this->cartRepository->checkValidation($cart, $request->location_id);
        if ($cart->items[0] == false) {
            return (['code' => 400, 'msg' => $cart->items[1]]);
        }
        $request->merge(array('cart' => json_encode($cart)));
        $service_names = '';
        //get the service names
        foreach ($cart->items as $cart_item) {
            $service_names .= $cart_item->service->name . ',';
        }
        // remove comma from the end of service name
        $service_names = rtrim($service_names, ",");
        return $this->sendDataToPortwallet($this->getTotalCartAmount($cart), $service_names, $customer, $request, "/checkout/place-order-final");
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
        $data['name'] = !empty($customer->profile->name) ? $customer->profile->name : 'N/A';
        $data['email'] = !empty($customer->profile->email) ? $customer->profile->email : 'N/A';
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
        if ($portwallet_response != null) {
            if ($portwallet_response->status == 200) {
                array_add($request, 'customer_id', $customer->id);
                Cache::forever('portwallet-payment-' . $portwallet_response->data->invoice_id, $request->all());
                Cache::forever('invoice-' . $portwallet_response->data->invoice_id, $portwallet_response->data->invoice_id);
                $url = $this->appPaymentUrl . $portwallet_response->data->invoice_id;
                return (['code' => 200, 'gateway_url' => $url]);
            }
        }
        return (['code' => 500, 'msg' => 'Payment Gateway connection failed']);
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
        $partner_order_payment->transaction_type = 'Debit';
        $partner_order_payment->method = 'Online';
        return $partner_order_payment;
    }

    public function sendConfirmation($customer, $order)
    {
        $customer = ($customer instanceof Customer) ? $customer : Customer::find($customer);
        if (!in_array($order->portal_name, config('sheba.stopped_sms_portal_for_customer'))) {
            (new SmsHandler('order-created'))
                ->setBusinessType(BusinessType::MARKETPLACE)
                ->setFeatureType(FeatureType::MARKET_PLACE_ORDER)
                ->send($customer->profile->mobile, [
                    'order_code' => $order->code()
                ]);
        }
        (new NotificationRepository())->send($order);
    }

    public function getTotalCartAmount($cart)
    {
        if (isset($cart->voucher_amount) && !empty($cart->voucher_amount)) {
            return $cart->price - $cart->voucher_amount;
        } else {
            return $cart->price;
        }
    }

    private function _setInfoCallId($order_info)
    {
        if (isset($order_info['info_call_id'])) {
            $info_call_id = $order_info['info_call_id'];
            if ($info_call_id != '' && $info_call_id != null) {
                $info_call = InfoCall::find($info_call_id);
                if ($info_call != null) {
                    if ($info_call->order == null) {
                        return $info_call_id;
                    }
                }
            }
        }
        return null;
    }

    private function _setAffiliationId($order_info)
    {
        if (isset($order_info['affiliation_id'])) {
            $affiliation_id = $order_info['affiliation_id'];
            if ($affiliation_id != '' && $affiliation_id != null) {
                $affiliation = Affiliation::find($affiliation_id);
                if ($affiliation != null) {
                    if ($affiliation->order == null) {
                        return $affiliation_id;
                    }
                }
            }
        }
        return null;
    }

    public function sendOnlinePaymentNotificationToDevice($token, bool $online_payment_status)
    {
        $msg = $online_payment_status ? 'Payment Successful' : 'Payment Unsuccessful';
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60 * 20);

        $notificationBuilder = new PayloadNotificationBuilder('Order Placement');
        $notificationBuilder->setBody($msg)
            ->setSound('default');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData(['online_payment_status' => $online_payment_status]);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

        FCM::sendTo($token, $option, $notification, $data);
    }
}
