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

class TransactionController extends Controller
{
    use ModificationFields;

    public function index($member, Request $request)
    {
        try {
            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);
            $transactions = BusinessTransaction::query()->orderBy('id', 'desc');
            if ($request->has('type'))
                $transactions = $transactions->type($request->type);

            $transactions = $transactions->get();

            $business_transaction = [];
            foreach ($transactions as $transaction) {
                $transaction = [
                    'id' => $transaction->id,
                    'date' => Carbon::parse($transaction->created_at)->format('Y'),
                    'sector' => 'SMS',
                    'amount' => $transaction->amount,
                    'type' => $transaction->type,
                    'log' => $transaction->log,
                ];
                array_push($business_transaction, $transaction);
            }
            return api_response($request, $business_transaction, 200, ['business_transaction' => $business_transaction]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}