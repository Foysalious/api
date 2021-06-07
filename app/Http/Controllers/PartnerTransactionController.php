<?php namespace App\Http\Controllers;

use App\Models\PartnerTransaction;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Sheba\PartnerPayment\PartnerPaymentValidatorFactory;
use Sheba\Reward\ActionRewardDispatcher;
use Sheba\Transactions\InvalidTransaction;
use Sheba\Transactions\Registrar;
use Validator;

class PartnerTransactionController extends Controller
{
    public function index($partner, Request $request)
    {
        try {
            $partner = $request->partner;
            list($offset, $limit) = calculatePagination($request);
            $transactions = $partner->transactions()->select('id', 'partner_id', 'type', 'balance', 'amount', 'log', 'created_at', 'partner_order_id')->get()->map(function ($transaction) {
                $transaction['is_bonus'] = 0;
                $transaction['valid_till'] = null;
                return $transaction;
            });
            $bonus_logs = $partner->bonusLogs()->with('spentOn')->get();
            $bonuses = collect();
            foreach ($bonus_logs as $bonus_log) {
                $bonuses->push($this->formatBonusTransaction($bonus_log));
            }
            $transactions = collect(array_merge($transactions->toArray(), $bonuses->toArray()));
            $transactions = $transactions->map(function ($transaction) {
                $transaction['amount'] = (double)$transaction['amount'];
                if (isset($transaction['balance'])) $transaction['balance'] = round($transaction['balance'], 2);
                return $transaction;
            })->sortByDesc('created_at');
            if ($request->has('month') && $request->has('year')) {
                $transactions = $transactions->filter(function ($transaction, $key) use ($request) {
                    $created_at = Carbon::parse($transaction['created_at']);
                    return ($created_at->month == $request->month && $created_at->year == $request->year);
                });
            }

            $final = array_slice($transactions->values()->all(), $offset, $limit);
            return count($final) > 0 ? api_response($request, $final, 200, [
                'transactions' => $final,
                'balance' => round($request->partner->totalWalletAmount(), 2),
                'credit' => round($request->partner->wallet, 2),
                'bonus' => round($request->partner->bonusWallet(), 2),
                'is_credit_limit_exceed'    => $request->partner->isCreditLimitExceed()
            ]) : api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function formatBonusTransaction($bonus)
    {
        return collect([
            'id' => $bonus->id,
            'partner_id' => $bonus->user_id,
            'type' => $bonus->type,
            'amount' => $bonus->amount,
            'log' => $bonus->log,
            'created_at' => $bonus->created_at->toDateTimeString(),
            'partner_order_id' => $bonus->spent_on_id,
            'is_bonus' => 1,
            'valid_till' => $bonus->valid_till ? $bonus->valid_till->format('d/m/Y') : null
        ]);
    }

    /**
     * @param Request $request
     * @param Registrar $registrar
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function payToSheba(Request $request, Registrar $registrar)
    {
        try {
            $this->validate($request, [
                'transaction_id' => 'required|string',
                'type' => 'required|in:bkash,rocket,mock',
            ]);

            $transaction = $registrar->register($request->partner, $request->type, $request->transaction_id);
            $request->merge(['transaction_amount' => $transaction['amount'], 'transaction_account' => $transaction['from_account']]);

            if ($res = $this->reconcile($request)) {
                if ($res->code != 200) return api_response($request, null, 500, ['message' => $res->msg]);
            } else {
                return api_response($request, null, 500);
            }
            /**
             * SHUT DOWN THIS EVENT BECAUSE IT USED OLD RECHARGE SYSTEM
             *
             * app(ActionRewardDispatcher::class)->run(
                'partner_wallet_recharge',
                $request->partner,
                $transaction['amount'],
                $request->partner
            );*/
            return api_response($request, null, 200, ['message' => "Wallet refilled."]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (InvalidTransaction $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 400, ['message' => $e->getMessage()]);
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
            $reconcile_url = config('sheba.admin_url') . '/api/partner/reconcile-collection';
            $res = json_decode($client->request('POST', $reconcile_url, [
                'form_params' => [
                    'resource_id' => $request->manager_resource->id,
                    'remember_token' => $request->manager_resource->remember_token,
                    'partner_id' => $request->partner->id,
                    'amount' => $request->transaction_amount,
                    'payment_token' => $payment_token,
                    'transaction_details' => json_encode([
                        'gateway' => $request->type,
                        'account' => [
                            'number' => $request->transaction_account
                        ],
                        'transaction' => [
                            'id' => $request->transaction_id,
                            'amount' => $request->transaction_amount
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

    public function validateTransactionId(Request $request)
    {
        if (!$request->has('transaction_id')) return api_response($request, null, 500, ['message' => 'Transaction ID is required']);
        $transaction = PartnerTransaction::hasTransactionID($request->transaction_id)->first();
        if (!empty($transaction)) return api_response($request, null, 400, ['message' => 'Transaction is not valid']);
        else return api_response($request, true, 200, ['transaction id is valid']);
    }
}
