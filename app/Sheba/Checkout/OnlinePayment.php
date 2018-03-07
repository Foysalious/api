<?php

namespace App\Sheba\Checkout;


use App\Library\PortWallet;
use App\Models\Order;
use App\Models\PartnerOrder;
use App\Models\PartnerOrderPayment;
use App\Sheba\UserRequestInformation;
use Redis;
use DB;

class OnlinePayment
{
    private $appBaseUrl;
    private $appPaymentUrl;
    private $portwallet;

    public function __construct()
    {
        $this->appBaseUrl = config('portwallet.app_base_url');
        $this->appPaymentUrl = config('portwallet.app_payment_url');
        $this->portwallet = new PortWallet(config('portwallet.app_key'), config('portwallet.app_secret'));
        $this->portwallet->setMode(config('portwallet.app_payment_mode'));
    }

    public function generatePortWalletLink(Order $order)
    {
        try {
            $order->calculate(true);
            $data = array();
            $data['amount'] = $order->totalPrice;
            $data['currency'] = "BDT";
            $data['product_name'] = "N/A";
            $data['product_description'] = "N/A";
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
                    Redis::set($redis_key, json_encode(['order_id' => $order->id, 'amount' => $order->totalPrice]));
                    Redis::expire($redis_key, 7200);
                    return $this->appPaymentUrl . $portwallet_response->data->invoice_id;
                }
            }
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function pay($online_payment_redis_key, $request)
    {
        $online_payment_data = json_decode($online_payment_redis_key);
        $portwallet_response = $this->portwallet->ipnValidate(array(
            'amount' => $online_payment_data->amount,
            'invoice' => $request->invoice,
            'currency' => "BDT"
        ));
        if ($portwallet_response->status == 200 && $portwallet_response->data->status == "ACCEPTED") {
            return $this->createPartnerOrderPayment($online_payment_data->order_id, $portwallet_response, $request);
        }
        return null;
    }

    private function createPartnerOrderPayment($order_id, $portwallet_response, $request)
    {
        try {
            $order = Order::find($order_id);
            $partner_order_payment = new PartnerOrderPayment();
            DB::transaction(function () use ($order, $partner_order_payment, $portwallet_response, $request) {
                $partner_order = $order->partner_orders[0];
                $partner_order->calculate(true);
                array_forget($partner_order, 'isCalculated');
                $partner_order->sheba_collection = $partner_order->totalPrice;
                $partner_order->update();
                $partner_order_payment->partner_order_id = $partner_order->id;
                $partner_order_payment->transaction_type = 'Debit';
                $partner_order_payment->method = 'Online';
                $partner_order_payment->amount = (double)$partner_order->sheba_collection;
                $partner_order_payment->log = 'advanced payment';
                $partner_order_payment->collected_by = 'Sheba';
                $partner_order_payment->created_by = $order->customer->id;
                $partner_order_payment->created_by_name = 'Customer - ' . $order->customer->id;
                $partner_order_payment->transaction_detail = json_encode($portwallet_response);
                $partner_order_payment->fill((new UserRequestInformation($request))->getInformationArray());
                $partner_order_payment->save();
            });
            return $partner_order_payment;
        } catch (\Throwable $e) {
            return null;
        }
    }
}