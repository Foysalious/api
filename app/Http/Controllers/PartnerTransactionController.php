<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

class PartnerTransactionController extends Controller
{

    public function index($partner, Request $request)
    {
        try {
            list($offset, $limit) = calculatePagination($request);
            $balance = 0;
            $transactions = $request->partner->transactions->each(function ($transaction, $key) use ($partner, &$balance) {
                $transaction->amount = (double)$transaction->amount;
                if ($transaction->type == 'Credit') {
                    $transaction['balance'] = $balance += $transaction->amount;
                } else {
                    $transaction['balance'] = $balance -= $transaction->amount;
                }
                removeRelationsFromModel($transaction);
            })->sortByDesc('id')->values()->all();
            $transactions = array_slice($transactions, $offset, $limit);
            return count($transactions) > 0 ? api_response($request, $transactions, 200, ['transactions' => $transactions, 'balance' => $request->partner->wallet]) : api_response($request, null, 404);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}