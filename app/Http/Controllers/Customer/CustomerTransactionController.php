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
            $this->validate($request, [
                'type' => 'sometimes|required|in:credit,debit',
            ]);
            list($offset, $limit) = calculatePagination($request);
            $partner = Partner::find(233);
            $transactions = $partner->transactions();
            if ($request->has('type')) $transactions->where('type', ucwords($request->type));
            $transactions = $transactions->orderBy('id', 'desc')->skip($offset)->take($limit)->get();
            return count($transactions) > 0 ? api_response($request, $transactions, 200, ['transactions' => $transactions, 'balance' => $partner->wallet]) : api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}