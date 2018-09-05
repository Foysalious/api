<?php

namespace App\Http\Controllers\Customer;


use App\Http\Controllers\Controller;
use App\Models\Partner;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CustomerTransactionController extends Controller
{
    public function index(Request $request)
    {
        try {
            list($offset, $limit) = calculatePagination($request);
            $balance = 0;
            $partner = Partner::find(233);
            $transactions = $partner->transactions->each(function ($transaction, $key) use ($partner, &$balance) {
                $transaction->amount = (double)$transaction->amount;
                if ($transaction->type == 'Credit') {
                    $transaction['balance'] = $balance += $transaction->amount;
                } else {
                    $transaction['balance'] = $balance -= $transaction->amount;
                }
                removeRelationsFromModel($transaction);
            })->sortByDesc('id');
            if ($request->has('month') && $request->has('year')) {
                $transactions = $transactions->filter(function ($transaction, $key) use ($request) {
                    $created_at = Carbon::parse($transaction->created_at);
                    return ($created_at->month == $request->month && $created_at->year == $request->year);
                });
            }
            $transactions = array_slice($transactions->values()->all(), $offset, $limit);
            return count($transactions) > 0 ? api_response($request, $transactions, 200, ['transactions' => $transactions, 'balance' => $partner->wallet]) : api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}