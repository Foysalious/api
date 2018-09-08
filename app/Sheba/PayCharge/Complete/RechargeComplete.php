<?php

namespace Sheba\PayCharge\Complete;


use Illuminate\Database\QueryException;
use Sheba\PayCharge\PayChargable;
use DB;

class RechargeComplete extends PayChargeComplete
{

    public function complete(PayChargable $pay_chargable, $method_response)
    {
        try {
            $class_name = $pay_chargable->userType;
            $user = $class_name::find($pay_chargable->userId);
            DB::transaction(function () use ($pay_chargable, $method_response, $user) {
                $user->creditWallet($pay_chargable->amount);
                $user->walletTransaction([
                    'amount' => $pay_chargable->amount, 'transaction_details' => $method_response,
                    'type' => 'Credit', 'log' => "$pay_chargable->amount BDT has been recharged to your wallet."
                ]);
            });
        } catch (QueryException $e) {
            app('sentry')->captureException($e);
            return null;
        }
    }
}