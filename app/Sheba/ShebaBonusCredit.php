<?php

namespace Sheba;

use App\Models\PartnerOrder;

class ShebaBonusCredit
{
    use ModificationFields;
    private $user;
    private $spent_model;

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    public function setSpentModel(PartnerOrder $partnerOrder)
    {
        $this->spent_model = $partnerOrder;
        return $this;
    }

    public function deduct($amount)
    {
        $bonuses = $this->user->bonuses()->where('status', 'valid')->orderBy('valid_till')->get();
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
        return $amount;
    }

    private function setSpentInfo($bonus)
    {
        if ($this->spent_model) {
            $bonus->spent_on_type = "App\\Models\\" . class_basename($this->spent_model);
            $bonus->spent_on_id = $this->spent_model->id;
        }
        return $bonus;
    }

    private function updateExistingBonus($bonus)
    {
        $bonus->status = 'used';
        $bonus = $this->setSpentInfo($bonus);
        $this->setModifier($this->user);
        $bonus->update();
    }

    private function createNewBonus($amount, $old_bonus)
    {
        $new_bonus = $old_bonus->replicate();
        $new_bonus->amount = $old_bonus->amount - $amount;
        $new_bonus->save();
    }
}