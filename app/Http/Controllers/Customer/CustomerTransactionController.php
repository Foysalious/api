<?php

namespace App\Http\Controllers\Customer;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerTransactionController extends Controller
{
    public function index(Request $request)
    {
        try {
            $this->validate($request, [
                'type' => 'sometimes|required|in:credit,debit'
            ]);
            list($offset, $limit) = calculatePagination($request);
            $customer = $request->customer;
            $transactions = $customer->transactions();
            if ($request->has('type')) $transactions->where('type', ucwords($request->type));
            $transactions = $transactions->select('id', 'customer_id', 'type', 'amount', 'log', 'created_at', 'partner_order_id')->orderBy('id', 'desc')->skip($offset)->take($limit)->get();
            $transactions->each(function ($transaction) {
                $transaction['valid_till'] = '2018/12/12';
                if ($transaction->partnerOrder) {
                    $transaction['category_name'] = $transaction->partnerOrder->jobs->first()->category->name;
                    $transaction['log'] = $transaction['category_name'];
                    $transaction['transaction_type'] = "Service Purchase";
                    $transaction['order_code'] = $transaction->partnerOrder->order->code();
                } else {
                    $transaction['category_name'] = $transaction['transaction_type'] = $transaction['order_code'] = "";
                    $transaction['transaction_type'] = $transaction['log'];
                }
                removeRelationsAndFields($transaction);
            });
            return api_response($request, $transactions, 200, ['transactions' => $transactions, 'balance' => $customer->wallet, 'credit' => $customer->wallet / 2, 'bonus' => $customer->wallet / 2]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}