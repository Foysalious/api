<?php

namespace Sheba\Usage;

use Illuminate\Support\Facades\Log;
use ReflectionClass;
use Sheba\AccountingEntry\Accounts\Accounts;
use Sheba\AccountingEntry\Repository\JournalCreateRepository;
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
        $duration = 0;
        foreach ($this->config as $index => $level) {
            $duration      += $level['duration'];
            $duration_pass = $usage >= $duration;
            $nid_pass=$level['nid_verification']?$this->user->isNIDVerified():true;
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
                $transaction = (new WalletTransactionHandler())->setModel($this->user->referredBy)->setSource(TransactionSources::SHEBA_WALLET)->setType(Types::credit())->setAmount($amount)->setLog("$amount BDT has been credited for partner referral from usage of name: " . $this->user->name . ', ID: ' . $this->user->id)->store();
                try {
                    $reference = (new \ReflectionClass($this->user->referredBy))->getShortName() ?? 'referral';
                } catch (\ReflectionException $e) {
                    $reference = 'referral';
                }
                Log::info(["checking refer", $amount, $level, $reference, $transaction]);
                $this->storeJournal($this->user->id, $transaction, $amount, $reference);
            }
            Log::info(["checking refer", $amount, $level, "amount is 0"]);
        }
    }

    private function storeJournal($typeId, $sourceType, $amount, $reference) {
        return (new JournalCreateRepository())->setTypeId($typeId)->setSource($sourceType)
            ->setAmount($amount)
            ->setDebitAccountKey((new Accounts())->asset->sheba::SHEBA_ACCOUNT)
            ->setCreditAccountKey((new Accounts())->income->reffer::REFFER)
            ->setDetails("Referral Bonus")
            ->setReference($reference)
            ->store();
    }
}
