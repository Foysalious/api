<?php


namespace App\Sheba\Reward\Event\Affiliate\Campaign\WalletRecharge;


use Illuminate\Support\Collection;
use Sheba\Reward\Event\Rule;
use Sheba\Reward\Exception\RulesTypeMismatchException;
use Sheba\Reward\Rewardable;
use Sheba\Reward\Event\Campaign;

class Event extends Campaign
{


    public function setRule(Rule $rule)
    {
        if (!($rule instanceof Rule))
            throw new RulesTypeMismatchException("Wallet recharge event must have an event rule");
        return parent::setRule($rule);
    }

    function findRewardableUsers(Collection $users)
    {
        // TODO: Implement findRewardableUsers() method.
    }

    /**
     * @inheritDoc
     */
    function checkProgress(Rewardable $rewardable)
    {
        // TODO: Implement checkProgress() method.
    }

    /**
     * @inheritDoc
     */
    function getParticipatedUsers()
    {
        // TODO: Implement getParticipatedUsers() method.
    }
}