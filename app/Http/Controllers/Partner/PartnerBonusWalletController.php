<?php

namespace App\Http\Controllers\Partner;


use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerBonusWalletController extends Controller
{
    public function transactions($partner, Request $request)
    {
        try {
            $partnerObj = Partner::findOrFail($partner);
            $bonusTransactions = $partnerObj->bonusLogs->map(function ($transaction) {
                return [
                    'created_at'    => $transaction->created_at,
                    'id'            => $transaction->id,
                    'type'          => $transaction->type,
                    'log'           => $transaction->log,
                    'amount'        => $transaction->amount ? $transaction->amount : "N/A",
                    'valid_till'    => $transaction->valid_till ? $transaction->valid_till : 'N/S',
                ];
            });

            return api_response($request, $bonusTransactions, 200, ['bonus_transaction' => $bonusTransactions]);

        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

}