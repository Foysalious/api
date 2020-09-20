<?php namespace Sheba\CmDashboard;

use App\Models\Partner;
use Illuminate\Support\Facades\DB;

class PartnerStatusCounter
{
    private $counter;
    private $probableExceedAmount = 1000;

    public function __construct()
    {
        $this->initialize();
    }

    private function initialize()
    {
        $this->counter = [
            'Verified'    => 0,
            'Unverified'  => 0,
            'Paused'      => 0,
            'Closed'      => 0,
            'Blacklisted' => 0,
            'Waiting'     => 0,
            'Onboarded'   => 0
        ];
    }

    public function get()
    {
        $counts = $this->countQuery()->get()->pluck('count', 'status')->toArray() + $this->counter;
        return $counts + [
            'WalletExceeded' => $this->getWalletExceededPartnerCount(),
            'ProbableToExceed' => $this->getProbableToWalletExceedPartnerCount()
        ];
    }

    private function countQuery()
    {
        return Partner::select('status', DB::raw('count(*) as count'))
            ->groupBy('status');
    }

    private function getWalletExceededPartnerCount()
    {
        return $this->walletSettingQuery()->whereRaw('partners.wallet < partner_wallet_settings.min_wallet_threshold')->count();
    }

    private function getProbableToWalletExceedPartnerCount()
    {
        return $this->walletSettingQuery()
            ->whereRaw('partners.wallet BETWEEN partner_wallet_settings.min_wallet_threshold AND partner_wallet_settings.min_wallet_threshold + ' . $this->probableExceedAmount)
            ->count();
    }

    private function walletSettingQuery()
    {
        return Partner::leftJoin('partner_wallet_settings', 'partners.id', '=', 'partner_wallet_settings.partner_id')->whereIn('status', ['Verified', 'Paused']);
    }
}