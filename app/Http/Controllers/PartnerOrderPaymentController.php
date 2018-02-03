<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PartnerOrderPaymentController extends Controller
{
    public function index(Request $request)
    {
        $partner = $request->partner;
        list($offset, $limit) = calculatePagination($request);
        $collections = $partner->payments->where('transaction_type', 'Debit')->sortByDesc('id')->splice($offset, $limit);
        dd($collections);
    }
}