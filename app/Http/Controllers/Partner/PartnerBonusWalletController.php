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
            $partner = Partner::findOrFail($partner);
            $bonusTransactions = $partner->bonusLogs->map(function ($transaction) use ($partner) {
                return [
                    'id'              => $transaction->id,
                    'partner_id'      => $partner->id,
                    'type'            => $transaction->type,
                    'amount'          => $transaction->amount ? $transaction->amount : "N/A",
                    'log'             => $transaction->log,
                    'portal_name'     => null,
                    'ip'              => null,
                    'user_agent'      => null,
                    'created_by_type' => null,
                    'created_by'      => $transaction->created_by,
                    'created_at'      => $transaction->created_at->toDateString(),
                    'valid_till'      => $transaction->valid_till->toDateString() ? $transaction->valid_till->toDateString() : 'N/S',
                ];
            });

            return api_response($request, $bonusTransactions, 200, ['bonus_transaction' => $bonusTransactions]);

        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

}