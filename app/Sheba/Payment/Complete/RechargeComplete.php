<?php

namespace Sheba\Payment\Complete;


use App\Sheba\Payment\Rechargable;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Sheba\Payment\PayChargable;
use DB;

class RechargeComplete extends PaymentComplete
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