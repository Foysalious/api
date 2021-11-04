<?php

namespace Sheba\Usage;

use App\Models\PartnerUsageHistory;
use App\Sheba\Usage\PartnerUsageUpgradeJob;
use Illuminate\Database\Query\Builder;
use Sheba\FraudDetection\TransactionSources;
use Sheba\ModificationFields;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class Usage
{
    use ModificationFields;

    private $user;
    private $config;
    private $type;

    public function __construct()
    {
        $this->config = config('partner.referral_steps');
    }

    public static function Partner()
    {
        return new Partner();
    }

    /**
     * @param mixed $type
     * @return Usage
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setUser($user)
    {
        $this->user = $user instanceof Builder? $user->first():$user;
        return $this;
    }

    public function create($modifier = null)
    {
        dispatch((new PartnerUsageUpgradeJob( $this->user, $modifier, $this->type)));
    }

    public static function isRefEnabled(){
        return !!config('partner.referral_enabled');
    }
    public function updateUserLevel()
    {
        $usage = PartnerUsageHistory::query()->where('partner_id',$this->user_id)->selectRaw('COUNT(DISTINCT(DATE(`partner_usages_history`.`created_at`))) as usages')->first();
        $usage = $usage ? $usage->usages : 0;
        $this->findAndUpgradeLevel($usage);

    }

    private function findAndUpgradeLevel($usage)
    {
        $duration = 0;
        foreach ($this->config as $index => $level) {
            $duration      += $level['duration'];
            $duration_pass = $usage >= $duration;
            $nid_pass= !$level['nid_verification'] || $this->user->isNIDVerified();
            if ($nid_pass&&$duration_pass) $this->upgradeLevel($index+1,true);
        }
        return -1;
    }

    private function upgradeLevel($level, $nid = false)
    {
        if ((is_null($this->user->refer_level)) || (int)$this->user->refer_level < $level) {
            $this->user->refer_level     = $level;
            $amount                      = ($this->config[$level - 1]['amount']);
            $this->user->referrer_income += $amount;
            $this->user->save();
            if ($amount > 0) {
                (new WalletTransactionHandler())->setModel($this->user->referredBy)->setSource(TransactionSources::SHEBA_WALLET)->setType(Types::credit())->setAmount($amount)->setLog("$amount BDT has been credited for partner referral from usage of name: " . $this->user->name . ', ID: ' . $this->user->id)->store();
            }
        }
    }
}
