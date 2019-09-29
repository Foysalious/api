<?php namespace Sheba\Reward;

use Sheba\ModificationFields;
use Sheba\Payment\PayableType;
use Sheba\Repositories\BonusLogRepository;

class BonusCredit
{
    use ModificationFields;
    private $user;
    /** @var PayableType */
    private $payable_type;

    private $logRepo;
    private $spent_model;
    public function __construct(BonusLogRepository $log_repo)
    {
        $this->logRepo = $log_repo;
    }

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    public function setPayableType(PayableType $payable_type)
    {
        $this->payable_type = $payable_type;
        return $this;
    }

    public function deduct($amount, $log = '')
    {
        $original_amount = $amount;
        $bonuses = $this->user->bonuses()->valid()->orderBy('valid_till')->get();
        foreach ($bonuses as $bonus) {
            if ($amount == 0) break;
            elseif ($bonus->amount >= $amount) {
                if ($bonus->amount != $amount) {
                    $this->createNewBonus($amount, $bonus);
                    $bonus->amount = $amount;
                }
                $amount = 0;
            } elseif ($bonus->amount < $amount) {
                $amount = $amount - $bonus->amount;
            }
            $this->updateExistingBonus($bonus);
        }

        if ($amount < $original_amount) $this->saveLog($original_amount - $amount, $log);

        return $amount;
    }

    public function setSpentModel($spent_on)
    {
        $this->spent_model = $spent_on;
        return $this;
    }

    private function saveLog($amount, $log = '')
    {
        $data = $this->getSpentInfo();
        $data['user_type'] = get_class($this->user);
        $data['user_id'] = $this->user->id;
        $data['amount'] = $amount;
        $data['log'] = $log;
        $data['valid_till'] = null;
        $this->logRepo->storeDebitLog($data);
    }


    private function getSpentInfo()
    {
        return [
            'spent_on_type' => get_class($this->payable_type),
            'spent_on_id' => $this->payable_type->id
        ];
    }

    private function updateExistingBonus($bonus)
    {
        $bonus->status = 'used';
        $bonus = $this->setSpentInfo($bonus);
        $this->setModifier($this->user);
        $bonus->update();
    }

    private function setSpentInfo($bonus)
    {
        if ($this->payable_type) {
            $data = $this->getSpentInfo();
            $bonus->spent_on_type = $data['spent_on_type'];
            $bonus->spent_on_id = $data['spent_on_id'];
        }
        return $bonus;
    }

    private function createNewBonus($amount, $old_bonus)
    {
        $new_bonus = $old_bonus->replicate();
        $new_bonus->amount = $old_bonus->amount - $amount;
        $new_bonus->save();
    }
}
