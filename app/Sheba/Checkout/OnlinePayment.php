<?php

namespace App\Sheba\Checkout;


use App\Library\PortWallet;
use App\Models\Order;
use App\Models\PartnerOrder;
use App\Models\PartnerOrderPayment;
use App\Repositories\NotificationRepository;
use App\Sheba\UserRequestInformation;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Redis;
use DB;

class OnlinePayment
{
    private $appBaseUrl;
    private $appPaymentUrl;
    private $portwallet;
    public $message = '';

    public function __construct()
    {
        $this->appBaseUrl = config('portwallet.app_base_url');
        $this->appPaymentUrl = config('portwallet.app_payment_url');
        $this->portwallet = new PortWallet(config('portwallet.app_key'), config('portwallet.app_secret'));
        $this->portwallet->setMode(config('portwallet.app_payment_mode'));
    }

    public function generatePortWalletLink(PartnerOrder $partnerOrder, $isAdvancedPayment = 0)
    {
        try {
            $partnerOrder->calculate(true);
            $data = array();
            $data['amount'] = $partnerOrder->due;
            $data['currency'] = "BDT";
            $data['product_name'] = "Sheba Services";
            $data['product_description'] = "Payment for Sheba";
            $data['name'] = "N/A";
            $data['email'] = "N/A";
            $data['phone'] = "N/A";
            $data['address'] = "N/A";
            $data['city'] = "city";
            $data['state'] = "state";
            $data['zipcode'] = "zipcode";
            $data['country'] = "BD";
            $data['redirect_url'] = $this->appBaseUrl . "/orders/online";
            $data['ipn_url'] = $this->appBaseUrl . "/ipn.php";
            if ($portwallet_response = $this->portwallet->generateInvoice($data)) {
                if ($portwallet_response->status == 200) {
                    $redis_key = 'portwallet-payment-' . $portwallet_response->data->invoice_id;
                    Redis::set($redis_key, json_encode(['amount' => $partnerOrder->due, 'partner_order_id' => $partnerOrder->id, 'isAdvancedPayment' => $isAdvancedPayment]));
                    Redis::expire($redis_key, 7200);
                    return $this->appPaymentUrl . $portwallet_response->data->invoice_id;
                }
            }
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function pay($data, $request)
    {
        $partner_order_id = $data->partner_order_id;
        $amount = $data->amount;
        $isAdvancedPayment = (int)$data->isAdvancedPayment;

        $portwallet_response = $this->portwallet->ipnValidate(array(
            'amount' => $amount,
            'invoice' => $request->invoice,
            'currency' => "BDT"
        ));
        if ($portwallet_response->status == 200 && $portwallet_response->data->status == "ACCEPTED") {
            $partnerOrder = PartnerOrder::find($partner_order_id);
            if ($partnerOrder) {
                $partnerOrder->calculate(true);
                array_forget($partnerOrder, 'isCalculated');
                if ($isAdvancedPayment) {
                    $partner_order_payment = $this->createPartnerOrderPayment($partnerOrder, $amount, $portwallet_response, $request);
                    if ($partner_order_payment) {
                        return array('success' => 1, 'isDue' => 0, 'message' => "Payment Successfully Received!", 'redirect_link' => $this->generateRedirectLink($partnerOrder, $isAdvancedPayment));
                    } else {
                        return array('success' => 0, 'redirect_link' => null,
                            'type' => 'ADVANCED_PAYMENT_DB_ERROR', 'isDue' => 0, 'message' => "Your payment has successfully received but there was a system error. Our Order Manager will contact with you shortly");
                    }
                } else {
                    $response = $this->clearSpPayment($partnerOrder, $amount, array_merge((new UserRequestInformation($request))->getInformationArray(), ['transaction_detail' => json_encode($portwallet_response)]));
                    if ($response) {
                        if ($response->code == 200) {
                            $notification = (new NotificationRepository())->forOnlinePayment($partnerOrder->id, $amount);
                            return array('success' => 1, 'isDue' => 0, 'message' => "Payment Successfully Received!", 'redirect_link' => $this->generateRedirectLink($partnerOrder, $isAdvancedPayment));
                        } else {
                            return array('success' => 0, 'isDue' => 0, 'type' => 'DB_ERROR', 'message' => "Your payment has successfully received but there was a system error. Our Order Manager will contact with you shortly");
                        }
                    }
                }
            }
            return array('success' => 0, 'isDue' => 0, 'type' => 'BILL_CLEAR_DB_ERROR', 'message' => "Your payment has successfully received but there was a system error. Our Order Manager will contact with you shortly");
        }
        return null;
    }

    public function ipnValidate($amount, $invoice)
    {
        $response = $this->portwallet->ipnValidate(array(
            'amount' => $amount,
            'invoice' => $invoice,
            'currency' => "BDT"
        ));
        return $response->status == 200 && $response->data->status == "ACCEPTED";
    }

    private function createPartnerOrderPayment(PartnerOrder $partnerOrder, $amount, $portwallet_response, $request)
    {
        try {
            $partner_order_payment = new PartnerOrderPayment();
            DB::transaction(function () use ($partnerOrder, $partner_order_payment, $portwallet_response, $request, $amount) {
                $partnerOrder->sheba_collection = $amount;
                $partnerOrder->update();
                $partner_order_payment->partner_order_id = $partnerOrder->id;
                $partner_order_payment->transaction_type = 'Debit';
                $partner_order_payment->method = 'Online';
                $partner_order_payment->amount = (double)$partnerOrder->sheba_collection;
                $partner_order_payment->log = 'advanced payment';
                $partner_order_payment->collected_by = 'Sheba';
                $partner_order_payment->created_by = $partnerOrder->order->customer->id;
                $partner_order_payment->created_by_type = "App\Models\Customer";
                $partner_order_payment->created_by_name = 'Customer - ' . $partnerOrder->order->customer->profile->name;
                $partner_order_payment->transaction_detail = json_encode($portwallet_response);
                $partner_order_payment->fill((new UserRequestInformation($request))->getInformationArray());
                $partner_order_payment->save();
            });
            return $partner_order_payment;
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return null;
        }
    }

    public function clearSpPayment(PartnerOrder $partnerOrder, $amount, $response)
    {
        try {
            $client = new Client();
            $res = $client->request('POST', env('SHEBA_BACKEND_URL') . '/api/partner-order/' . $partnerOrder->id . '/collect',
                [
                    'form_params' => array_merge([
                        'customer_id' => $partnerOrder->order->customer->id,
                        'remember_token' => $partnerOrder->order->customer->remember_token,
                        'sheba_collection' => (double)$amount,
                        'payment_method' => 'Online',
                        'created_by_type' => 'App\Models\Customer',
                    ], $response)
                ]);
            return json_decode($res->getBody());
        } catch (RequestException $e) {
            app('sentry')->captureException($e);
            return false;
        }
    }

    public function generateRedirectLink(PartnerOrder $partnerOrder, $isAdvancedPayment)
    {
        $s_id = str_random(10);
        Redis::set($s_id, 'online');
        Redis::expire($s_id, 500);
        if ($isAdvancedPayment) {
            return env('SHEBA_FRONT_END_URL') . '/profile/orders?s_token=' . $s_id;
        } else {
            return env('SHEBA_FRONT_END_URL') . '/orders/' . $partnerOrder->jobs[0]->id . '/bill?s_token=' . $s_id;
        }
    }

    public function generateSSLLink(Order $order, $isAdvancedPayment = 0)
    {
        $partnerOrder = $order->partnerOrders[0];
        $partnerOrder->calculate(true);
        $transaction_id = "SHEBA_ORDER_" . $order->id . "_" . uniqid();
        $data = array();
        $data['store_id'] = config('ssl.store_id');
        $data['store_passwd'] = config('ssl.store_password');
        $data['total_amount'] = (double)$partnerOrder->due;
        $data['currency'] = "BDT";
        $data['success_url'] = config('ssl.success_url');
        $data['fail_url'] = config('ssl.fail_url');
        $data['cancel_url'] = config('ssl.cancel_url');
        $data['emi_option'] = 0;
        $data['tran_id'] = $transaction_id;
        $data['cus_name'] = $order->customer->profile->name;
        $data['cus_email'] = $order->customer->profile->email;
        $data['cus_phone'] = $order->customer->profile->mobile;
        $result = $this->getSslSession($data);
        if ($result) {
            if ($result->status == "SUCCESS") {
                Redis::set($transaction_id, json_encode(['amount' => $partnerOrder->due, 'partner_order_id' => $partnerOrder->id,
                    'isAdvancedPayment' => $isAdvancedPayment, 'success' => 0, 'message' => $this->message]));
                Redis::expire($transaction_id, 7200);
                return $result->GatewayPageURL;
            }
        }
        return null;
    }

    public function getSslSession($data)
    {
        try {
            $client = new Client();
            $result = $client->request('POST', config('ssl.session_url'), ['form_params' => $data]);
            return json_decode($result->getBody());
        } catch (RequestException $e) {
            app('sentry')->captureException($e);
            return null;
        }
    }

    public function clearPayment($transaction, $payment_gateway_response, $request)
    {
        $partnerOrder = PartnerOrder::find($transaction->partner_order_id);
        $isAdvancedPayment = (int)$transaction->isAdvancedPayment;
        $amount = (double)$transaction->amount;
        if ($partnerOrder) {
            $partnerOrder->calculate(true);
            array_forget($partnerOrder, 'isCalculated');
            if ($partnerOrder->due == 0) {
                $notification = (new NotificationRepository())->forOnlinePayment($partnerOrder->id, $amount);
                $this->message = "Payment Successfully Received!";
                return $this->generateRedirectLink($partnerOrder, $isAdvancedPayment);
            }
            if ($isAdvancedPayment) {
                $partner_order_payment = $this->createPartnerOrderPayment($partnerOrder, $amount, $payment_gateway_response, $request);
                if ($partner_order_payment) {
                    $notification = (new NotificationRepository())->forOnlinePayment($partnerOrder->id, $amount);
                    $this->message = "Payment Successfully Received!";
                    return $this->generateRedirectLink($partnerOrder, $isAdvancedPayment);
                } else {
                    $this->message = "Your payment has successfully received but there was a system error. Our Order Manager will contact with you shortly";
                    return null;
                }
            } else {
                $response = $this->clearSpPayment($partnerOrder, $amount, array_merge((new UserRequestInformation($request))->getInformationArray(), ['transaction_detail' => json_encode($payment_gateway_response)]));
                if ($response) {
                    if ($response->code == 200) {
                        $this->message = "Payment Successfully Received!";
                        $notification = (new NotificationRepository())->forOnlinePayment($partnerOrder->id, $amount);
                        return $this->generateRedirectLink($partnerOrder, $isAdvancedPayment);
                    } else {
                        $this->message = "Your payment has successfully received but there was a system error. Our Order Manager will contact with you shortly";
                        return null;
                    }
                }
            }
        }
        $this->message = "Order not found!";
        return null;
    }
}