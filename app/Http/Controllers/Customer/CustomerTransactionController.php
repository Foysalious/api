<?php

namespace App\Http\Controllers\Customer;


use App\Http\Controllers\Controller;
use App\Models\Customer;
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
            /** @var Customer $customer */
            $customer = $request->customer;
            $transactions = $customer->transactions();
            $bonus_logs = $customer->bonusLogs();
            if ($request->has('type')) {
                $transactions->where('type', ucwords($request->type));
                $bonus_logs->where('type', ucwords($request->type));
            }
            $transactions = $transactions->select('id', 'customer_id', 'type', 'amount', 'log', 'created_at', 'partner_order_id', 'created_at')
                ->with('partnerOrder.order')->orderBy('id', 'desc')->skip($offset)->take($limit)->get();
            $bonus_logs = $bonus_logs->with('spentOn')->skip($offset)->take($limit)->get();
            $transactions->each(function ($transaction) {
                $transaction['valid_till'] = null;
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
            foreach ($bonus_logs as $bonus_log) {
                if ($bonus_log->type == 'Credit') $transactions = $this->formatCreditBonusTransaction($bonus_log, $transactions);
                else $transactions = $this->formatDebitBonusTransaction($bonus_log, $transactions);
            }
            $transactions = $transactions->sortByDesc('created_at')->splice($offset, $limit)->values()->all();
            return api_response($request, $transactions, 200, [
                'transactions' => $transactions, 'balance' => $customer->shebaCredit(),
                'credit' => round($customer->wallet, 2), 'bonus' => round($customer->shebaBonusCredit(), 2)]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function formatDebitBonusTransaction($bonus, $transactions)
    {
        $category = $bonus->spent_on->jobs->first()->category;
        $transactions->push(array(
            'id' => $bonus->id,
            'customer_id' => $bonus->user_id,
            'type' => 'Debit',
            'amount' => $bonus->amount,
            'log' => $category->name,
            'created_at' => $bonus->created_at->toDateTimeString(),
            'partner_order_id' => $bonus->spent_on_id,
            'valid_till' => null,
            'order_code' => $bonus->spent_on->order->code(),
            'transaction_type' => 'Service Purchase',
            'category_name' => $category->name,
        ));
        return $transactions;
    }

    private function formatCreditBonusTransaction($bonus, $transactions)
    {
        $transactions->push(array(
            'id' => $bonus->id,
            'customer_id' => $bonus->user_id,
            'type' => 'Credit',
            'amount' => $bonus->amount,
            'log' => $bonus->log,
            'created_at' => $bonus->created_at->toDateTimeString(),
            'partner_order_id' => null,
            'valid_till' => $bonus->valid_till->format('d/m/Y'),
            'order_code' => '',
            'transaction_type' => $bonus->log,
            'category_name' => '',
        ));
        return $transactions;
    }
}