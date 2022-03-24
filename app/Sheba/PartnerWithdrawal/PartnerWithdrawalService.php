<?php

namespace Sheba\PartnerWithdrawal;

use App\Models\Partner;
use App\Models\WithdrawalRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PartnerWithdrawalService
{
    /** @var WalletSettingUpdater */
    private $updater;

    public function __construct(WalletSettingUpdater $updater)
    {
        $this->updater = $updater;
    }

    public function store(Partner $partner, array $data)
    {
        $pendingAmount = $partner->walletSetting->pending_withdrawal_amount;
        $pendingAmount = $pendingAmount + $data['amount'];
//        if ($creditLimitAmount < 0) {
//            $creditLimitAmount = $data['amount']; // assuming min_wallet_threshold is less than 0 so we converted it to zero.
//        } else {
//            $creditLimitAmount = $creditLimitAmount + $data['amount'];
//        }
//        $creditLimitData = [
//            'min_wallet_threshold' => $creditLimitAmount,
//            'reset_credit_limit_after' => null,
//            'log' => 'automatically updated credit limit because of withdrawal request'
//        ];

        $newWithdrawal = WithdrawalRequest::create($data);
        $this->updateSetting($partner, ['pending_withdrawal_amount'=> $pendingAmount]);
        return $newWithdrawal;
    }

    public function updateSetting(Partner $partner, $data)
    {
        $this->updater->setSetting($partner->walletSetting)->setData($data)->update();
    }

    public function hasPendingBkashRequest($partner)
    {
        return WithdrawalRequest::where('payment_method', 'bkash')->whereIn('status', ['pending', 'approval_pending'])->where('requester_id', $partner->id)->first();
    }

    public function doesExceedWithdrawalLimit($amount, $paymentMethod) : array
    {
        $limitBkash = constants('WITHDRAW_LIMIT')['bkash'];
        if ($paymentMethod == 'bkash' && ($amount < $limitBkash['min'] || $amount > $limitBkash['max']))
            return ['status' => true, 'msg' => 'Payment Limit mismatch for bkash minimum limit ' . $limitBkash['min'] . ' TK and maximum ' . $limitBkash['max'] . ' TK'];

        $limitBank  = constants('WITHDRAW_LIMIT')['bank'];
        if ($paymentMethod == 'bank' && ($amount < $limitBank['min'] || (double)$amount > $limitBank['max']))
            return ['status' => true, 'msg' => 'Payment Limit mismatch for bank minimum limit ' . $limitBank['min'] . ' TK and maximum ' . $limitBank['max'] . ' TK'];

        return ['status' => false];
    }

    public function doesExceedWithdrawalAmountForOrder($amount, $orderid, $partnerOrder): bool
    {
        return $partnerOrder->sheba_collection == 0
            || $amount > $partnerOrder->sheba_collection
            || (($this->activeRequestAgainstPartnerOrderAmount($partnerOrder) + $amount) > $partnerOrder->sheba_collection);
    }

    public function activeRequestAgainstPartnerOrderAmount($partner_order)
    {
        $withdrawalRequest = WithdrawalRequest::select(DB::raw('sum(amount) as total_amount'))
            ->active()
            ->where('order_id', $partner_order->order_id)
            ->where('requester_type', 'partner')
            ->where('requester_id', $partner_order->partner_id)
            ->first();

        return $withdrawalRequest->total_amount ?? 0;
    }
}