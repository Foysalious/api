<?php


namespace Sheba\Reward\Event\Affiliate\Campaign\WalletRecharge;


use Sheba\Reward\Event\Affiliate\Campaign\WalletRecharge\Parameter\Gateway;
use Sheba\Reward\Event\Affiliate\Campaign\WalletRecharge\Parameter\RechargeStatus;
use Sheba\Reward\Event\Affiliate\Campaign\WalletRecharge\Parameter\Target;
use Illuminate\Database\Eloquent\Builder;
use Sheba\Reward\Event\TargetProgress;
use Sheba\Reward\Event\CampaignRule;

class Rule extends CampaignRule
{
    /** @var Gateway */
    public $gateway;
    /** @var RechargeStatus */
    public $rechargeStatus;

    public function validate()
    {
        $this->gateway->validate();
        $this->rechargeStatus->validate();
    }

    public function makeParamClasses()
    {
        $this->target = new Target();
        $this->gateway = new Gateway();
        $this->rechargeStatus = new RechargeStatus();
    }

    public function setValues()
    {
        $this->rechargeStatus->value = property_exists($this->rule, 'recharge_status') ? $this->rule->recharge_status : null;
        $this->gateway->value = property_exists($this->rule, 'gateway') ? $this->rule->gateway : null;
    }

    public function check(Builder $query)
    {
        $this->rechargeStatus->check($query);
        $this->gateway->check($query);

    }

    /**
     * @inheritDoc
     */
    public function checkParticipation(Builder $query)
    {
        $this->rechargeStatus->check($query);
        $this->gateway->check($query);
        $this->target->check($query);
    }

    public function getProgress(Builder $query): TargetProgress
    {
        // TODO: Implement getProgress() method.
    }

    public function isTargetAchieved($achieved_value)
    {
        return $achieved_value >= $this->target->value;
    }

    public function getAchievedValue($total_amount)
    {
        return $total_amount > $this->target->value ? $this->target->value : $total_amount ;
    }
}