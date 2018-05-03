<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Sheba\PartnerPayment\PartnerPaymentValidatorFactory;
use Validator;

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
            })->sortByDesc('id');
            if ($request->has('month') && $request->has('year')) {
                $transactions = $transactions->filter(function ($transaction, $key) use ($request) {
                    return ($transaction->created_at->month == $request->month) && ($transaction->created_at->year == $request->year);
                });
            }
            $transactions = array_slice($transactions->values()->all(), $offset, $limit);
            return count($transactions) > 0 ? api_response($request, $transactions, 200, ['transactions' => $transactions, 'balance' => $request->partner->wallet]) : api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function payToSheba(Request $request)
    {
        try {
            $this->validate($request, [
                'amount' => 'required|numeric|min:1',
                'transaction_id' => 'required',
                'account' => 'required',
                'type' => 'required|in:bkash,rocket,mock',
            ]);
            $payment_validator = PartnerPaymentValidatorFactory::make($request->all());
            if ($error = $payment_validator->hasError()) {
                return api_response($request, null, 400, ['message' => $error]);
            }
            if ($res = $this->reconcile($request)) {
                if ($res->code != 200) return api_response($request, null, 500, ['message' => $res->msg]);
            } else {
                return api_response($request, null, 500);
            }
            return api_response($request, null, 200, ['message' => "Wallet refilled."]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function reconcile(Request $request)
    {
        try {
            $expires_at = Carbon::now()->addMinutes(2);
            $cache_name = "partner_" . $request->partner->id . "_payment_reconcile_token";
            \Cache::store('redis')->put($cache_name, $payment_token = Str::random(32), $expires_at);

            $client = new Client();
            $reconcile_url = env('SHEBA_BACKEND_URL') . '/api/partner/reconcile-collection';
            $res = json_decode($client->request('POST', $reconcile_url, [
                'form_params' => [
                    'resource_id' => $request->manager_resource->id,
                    'remember_token' => $request->manager_resource->remember_token,
                    'partner_id' => $request->partner->id,
                    'amount' => $request->amount,
                    'payment_token' => $payment_token,
                    'transaction_details' => json_encode([
                        'gateway' => $request->type,
                        'account' => [
                            'number' => $request->account
                        ],
                        'transaction' => [
                            'id' => $request->transaction_id,
                            'amount' => $request->amount
                        ],
                    ]),
                ]
            ])->getBody());

            return $res;
        } catch (RequestException $e) {
            app('sentry')->captureException($e);
            return null;
        }
    }
}