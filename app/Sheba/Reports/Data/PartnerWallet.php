<?php namespace Sheba\Reports\Data;

use App\Models\Partner;
use Sheba\Queries\PartnerTransaction\Deposit;
use Sheba\Reports\ReportData;

class PartnerWallet extends ReportData
{
    public function get()
    {
        $partners_with_wallet = [];
        $partners = Partner::with('walletSetting', 'withdrawalRequests')->whereIn('status', ['Verified', 'Paused'])->get();
        $partners_deposit = (new Deposit())->last()->pluckMultiple(['amount', 'created_at'], 'partner_id')->toArray();
        foreach ($partners as $partner) {
            /** @var Partner $partner */
            $last_withdrawn = $partner->withdrawalRequests->where('status', 'completed')->sortByDesc('created_at')->first();
            $partners_with_wallet[] = [
                'sp_id' => $partner->id,
                'sp_name' => $partner->name,
                'sp_contact_number' => $partner->getContactNumber() ?: 'N/S',
                'sp_status' => $partner->status,
                'wallet_amount' => $partner->wallet,
                'bonus' => $partner->bonus_credit,
                'credit_limit' => $partner->walletSetting->min_wallet_threshold,
                'credit_limit_status' => ($partner->wallet < $partner->walletSetting->min_wallet_threshold) ? "Exceeded" : "OK",
                'last_withdrawn_amount' => $last_withdrawn ? $last_withdrawn->amount : 'N/S',
                'last_withdrawn' => $last_withdrawn ? $last_withdrawn->created_at->format('d-M-y') : 'N/S',
                'wallet_status' => $partner->wallet[0] == '-' ? 'SHEBA Receivable' : 'SHEBA Payable',
                'minimum_balance' => $partner->walletSetting ? $partner->walletSetting->security_money : 'N/S',
                'is_minimum_balance_received' => $partner->walletSetting ? ($partner->walletSetting->security_money_received ? 'Yes' : 'No') : 'N/S',
                'last_deposited_amount' => isset($partners_deposit[$partner->id]) ? $partners_deposit[$partner->id]['amount'] : 'N/S',
                'last_deposited_date' => isset($partners_deposit[$partner->id]) ? $partners_deposit[$partner->id]['created_at']->format('d-M-y') : 'N/S'
            ];
        }
        return $partners_with_wallet;
    }
}  