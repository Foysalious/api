<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

class PartnerTransactionController extends Controller
{

    public function index($partner, Request $request)
    {
        try {
            $partner = $request->partner;
            list($offset, $limit) = calculatePagination($request);
            $partner->load(['transactions' => function ($q) use ($offset, $limit) {
                $q->orderBy('partner_transactions.id');
            }]);
            $balance = 0;
            $transactions = $partner->transactions->each(function ($transaction, $key) use ($partner, &$balance) {
                $transaction->amount = (double)$transaction->amount;
                if ($transaction->type == 'Credit') {
                    $transaction['balance'] = $balance += $transaction->amount;
                } else {
                    $transaction['balance'] = $balance -= $transaction->amount;
                }
                removeRelationsFromModel($transaction);
                removeSelectedFieldsFromModel($transaction);
            })->sortByDesc('id')->values()->all();
            return count($transactions) > 0 ? api_response($request, $transactions, 200, ['transactions' => $transactions]) : api_response($request, null, 404);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}