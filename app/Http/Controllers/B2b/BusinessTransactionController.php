<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\BusinessTransaction;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

class BusinessTransactionController extends Controller
{
    use ModificationFields;

    public function index($business, Request $request)
    {
        try {
            $business = $request->business;
            list($offset, $limit) = calculatePagination($request);
            $transactions = BusinessTransaction::where('business_id', (int)$business->id)->orderBy('id', 'desc')->skip($offset)->limit($limit);
            if ($request->filled('type'))
                $transactions = $transactions->type($request->type);
            if ($request->filled('sector'))
                $transactions = $transactions->tag($request->sector);

            $start_date = $request->filled('start_date') ? $request->start_date : null;
            $end_date = $request->filled('end_date') ? $request->end_date : null;
            if ($start_date && $end_date) {
                $transactions->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
            }
            $transactions = $transactions->get();

            $business_transaction = [];
            foreach ($transactions as $transaction) {
                $transaction = [
                    'id' => $transaction->id,
                    'date' => Carbon::parse($transaction->created_at)->format('d/m/y'),
                    'sector' => $transaction->tag,
                    'amount' => $transaction->amount,
                    'wallet' => (double)$business->wallet,
                    'type' => $transaction->type,
                    'log' => $transaction->log,
                ];
                array_push($business_transaction, $transaction);
            }

            if (count($business_transaction) > 0) return api_response($request, $business_transaction, 200, ['business_transaction' => $business_transaction]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}