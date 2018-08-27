<?php

namespace Sheba\OnlinePayment;


use App\Models\Order;
use App\Models\PartnerOrder;
use App\Models\PartnerOrderPayment;
use App\Sheba\UserRequestInformation;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Redis;
use DB;

class Payment
{
    private $order;
    private $paymentGateway;

    public function __construct(Order $order, PaymentGateway $paymentGateway)
    {
        $this->order = $order;
        $this->paymentGateway = $paymentGateway;
    }

    public function generateLink($isAdvancePayment)
    {
        return $this->paymentGateway->generateLink($this->order, $isAdvancePayment);
    }

    public function success(Request $request)
    {
        $payment_info = Redis::get("$request->paymentID");
        $payment_info = json_decode($payment_info);
        $result_data = $this->paymentGateway->success($request);
        if ($result_data && !isset($result_data->errorCode)) {
            $payment_info->trxID = $result_data->trxID;
            $payment_info->transactionStatus = $result_data->transactionStatus;
            $partnerOrder = PartnerOrder::find($payment_info->partner_order_id);
            if ($payment_info->isAdvancedPayment) {
                $payment_clear = $this->createPartnerOrderPayment($partnerOrder, $payment_info->amount, $payment_info, $request);
            } else {
                $response = array_merge((new UserRequestInformation($request))->getInformationArray(), ['transaction_detail' => json_encode($payment_info)]);
                $payment_clear = $this->clearSpPayment($partnerOrder, $payment_info->amount, $response);
            }
            if ($payment_clear) {
                Redis::del("$request->paymentID");
                return true;
            }
        }
        return false;
    }

    private function createPartnerOrderPayment(PartnerOrder $partner_order, $amount, $gateway_response, $request)
    {
        try {
            $partner_order_payment = new PartnerOrderPayment();
            DB::transaction(function () use ($partner_order, $partner_order_payment, $gateway_response, $amount, $request) {
                $partner_order->sheba_collection = $amount;
                $partner_order->update();
                $partner_order_payment->partner_order_id = $partner_order->id;
                $partner_order_payment->transaction_type = 'Debit';
                $partner_order_payment->method = 'bkash';
                $partner_order_payment->amount = (double)$partner_order->sheba_collection;
                $partner_order_payment->log = 'advanced payment';
                $partner_order_payment->collected_by = 'Sheba';
                $partner_order_payment->created_by = $partner_order->order->customer_id;
                $partner_order_payment->created_by_type = "App\Models\Customer";
                $partner_order_payment->created_by_name = 'Customer - ' . $partner_order->order->customer->profile->name;
                $partner_order_payment->transaction_detail = $this->paymentGateway->formatTransactionData($gateway_response);
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
            $res = $client->request('POST', config('sheba.admin_url') . '/api/partner-order/' . $partnerOrder->id . '/collect',
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
}