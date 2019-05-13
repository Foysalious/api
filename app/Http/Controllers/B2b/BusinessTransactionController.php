<?php namespace App\Http\Controllers\B2b;

use App\Models\BusinessTransaction;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\BusinessMember;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\Member;
use Carbon\Carbon;
use DB;

class BusinessTransactionController extends Controller
{
    use ModificationFields;

    public function index($business, Request $request)
    {
        try {
            $business = $request->business;
            $transactions = BusinessTransaction::where('business_id', (int)$business->id)->orderBy('id', 'desc');
            if ($request->has('type'))
                $transactions = $transactions->type($request->type);

            $transactions = $transactions->get();

            $business_transaction = [];
            foreach ($transactions as $transaction) {
                $transaction = [
                    'id' => $transaction->id,
                    'date' => Carbon::parse($transaction->created_at)->format('Y'),
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