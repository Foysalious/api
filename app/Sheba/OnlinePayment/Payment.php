<?php

namespace Sheba\OnlinePayment;

use App\Models\PartnerOrder;
use App\Models\PartnerOrderPayment;
use App\Sheba\UserRequestInformation;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Redis;
use DB;

class Payment
{
    private $partnerOrder;
    private $paymentGateway;

    public function __construct(PartnerOrder $partnerOrder, PaymentGateway $paymentGateway)
    {
        $this->partnerOrder = $partnerOrder;
        $this->paymentGateway = $paymentGateway;
    }

    public function generateLink($isAdvancePayment)
    {
        return $this->paymentGateway->generateLink($this->partnerOrder, (int)$isAdvancePayment);
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

    private function createPartnerOrderPayment(PartnerOrder $partnerOrder, $amount, $gateway_response, $request)
    {
        $partner_order_payment = new PartnerOrderPayment();
        try {
            DB::transaction(function () use ($partnerOrder, $partner_order_payment, $gateway_response, $amount, $request) {
                $partnerOrder->sheba_collection = $amount;
                $partnerOrder->update();
                $partner_order_payment->partner_order_id = $partnerOrder->id;
                $partner_order_payment->transaction_type = 'Debit';
                $partner_order_payment->method = 'bkash';
                $partner_order_payment->amount = (double)$partnerOrder->sheba_collection;
                $partner_order_payment->log = 'advanced payment';
                $partner_order_payment->collected_by = 'Sheba';
                $partner_order_payment->created_by = $partnerOrder->order->customer_id;
                $partner_order_payment->created_by_type = "App\Models\Customer";
                $partner_order_payment->created_by_name = 'Customer - ' . $partnerOrder->order->customer->profile->name;
                $partner_order_payment->transaction_detail = $this->paymentGateway->formatTransactionData($gateway_response);
                $partner_order_payment->fill((new UserRequestInformation($request))->getInformationArray());
                $partner_order_payment->save();
            });
        } catch (QueryException $e) {
            app('sentry')->captureException($e);
            return null;
        }
        return $partner_order_payment;
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