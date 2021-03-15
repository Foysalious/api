<?php


namespace App\Sheba\Reward\Event\Affiliate\Campaign\WalletRecharge;


use App\Sheba\Reward\Event\Affiliate\Campaign\WalletRecharge\Parameter\Gateway;
use App\Sheba\Reward\Event\Affiliate\Campaign\WalletRecharge\Parameter\RechargeStatus;
use App\Sheba\Reward\Event\Affiliate\Campaign\WalletRecharge\Parameter\Target;
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
        // TODO: Implement check() method.
    }

    /**
     * @inheritDoc
     */
    public function getProgress(Builder $query): TargetProgress
    {
        // TODO: Implement getProgress() method.
    }
}