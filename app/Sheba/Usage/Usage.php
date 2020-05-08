<?php

namespace Sheba\Usage;

use Sheba\FraudDetection\TransactionSources;
use Sheba\ModificationFields;
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
        $this->user = $user;
        return $this;
    }

    public function create($modifier = null)
    {
        if (empty($this->type))
            return 0;
        $data = ['type' => $this->type];
        if (!empty($modifier))
            $this->setModifier($modifier);
        $history = $this->user->usage()->create($this->withCreateModificationField($data));
        if (!empty($this->user->referredBy))
            $this->updateUserLevel();
        return $history;
    }

    private function updateUserLevel()
    {
        $usage = $this->user->usage()->selectRaw('COUNT(DISTINCT(DATE(`partner_usages_history`.`created_at`))) as usages')->first();
        $usage = $usage ? $usage->usages : 0;
        $this->findAndUpgradeLevel($usage);

    }

    private function findAndUpgradeLevel($usage)
    {
        foreach ($this->config as $index => $level) {
            if ($level['nid_verification'] && !$this->user->isNIDVerified()){
                return -1;
            }
            if (($level['nid_verification'] && !!$this->user->isNIDVerified())) {
                $this->upgradeLevel($index + 1, true);
            }
            if ($usage >= $level['duration'] && !$level['nid_verification']) {
                $this->upgradeLevel($index + 1);
            }
        }
        return -1;
    }

    private function upgradeLevel($level, $nid = false)
    {
        if ((is_null($this->user->refer_level)) || (int)$this->user->refer_level < $level) {
            $this->user->refer_level     = $level;
            $amount                      = ($this->config[$level]['amount']);
            $this->user->referrer_income += $amount;
            $this->user->save();
            (new WalletTransactionHandler())->setModel($this->user->referredBy)->setSource(TransactionSources::SHEBA_WALLET)->setType('credit')->setAmount($amount)->setLog("$amount BDT has been credited for partner referral from usage of name: " . $this->user->name . ', ID: ' . $this->user->id)->store();
        }
    }
}
