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
                $q->orderBy('partner_transactions.id', 'desc');
            }]);
            $transactions = $partner->transactions->each(function ($transaction, $key) use ($partner) {
                $transaction->amount = (double)$transaction->amount;
                $credit = $partner->transactions->filter(function ($item, $key) use ($transaction) {
                    return $item->id <= $transaction->id && $item->type == 'Credit';
                })->sum('amount');
                $debit = $partner->transactions->filter(function ($item, $key) use ($transaction) {
                    return $item->id <= $transaction->id && $item->type == 'Debit';
                })->sum('amount');
                $transaction['balance'] = $credit - $debit;
                removeRelationsFromModel($transaction);
                removeSelectedFieldsFromModel($transaction);
            });
            return count($transactions) > 0 ? api_response($request, $transactions, 200, ['transactions' => $transactions]) : api_response($request, null, 404);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}