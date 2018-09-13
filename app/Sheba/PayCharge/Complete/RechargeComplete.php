<?php

namespace Sheba\PayCharge\Complete;


use App\Sheba\PayCharge\Rechargable;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Sheba\PayCharge\PayChargable;
use DB;

class RechargeComplete extends PayChargeComplete
{

    public function complete(PayChargable $pay_chargable, $method_response)
    {
        try {
            $class_name = $pay_chargable->userType;
            /** @var Rechargable $user */
            $user = $class_name::find($pay_chargable->userId);
            DB::transaction(function () use ($pay_chargable, $method_response, $user) {
                $user->rechargeWallet($pay_chargable->amount, [
                    'amount' => $pay_chargable->amount, 'transaction_details' => json_encode($method_response['details']),
                    'type' => 'Credit', 'log' => 'Credit Purchase'
                ]);
            });
        } catch (QueryException $e) {
            throw $e;
        }
        return true;
    }
}