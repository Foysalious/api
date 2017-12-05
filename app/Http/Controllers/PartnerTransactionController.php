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
            $transactions = $partner->transactions->each(function ($item, $key) {
                $item->amount = (double)$item->amount;
                removeRelationsFromModel($item);
                removeSelectedFieldsFromModel($item);
            });
            return count($transactions) > 0 ? api_response($request, $transactions, 200, ['transactions' => $transactions]) : api_response($request, null, 404);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}