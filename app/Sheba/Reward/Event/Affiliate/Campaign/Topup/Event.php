<?php


namespace App\Sheba\Reward\Event\Affiliate\Campaign\Topup;


use Illuminate\Support\Collection;
use Sheba\Reward\Event\Campaign;
use Sheba\Reward\Event\Rule;
use Sheba\Reward\Exception\RulesTypeMismatchException;
use Sheba\Reward\Rewardable;

class Event extends Campaign
{
    private $query;

    public function setRule(Rule $rule)
    {
        if (!($rule instanceof Rule))
            throw new RulesTypeMismatchException("TopUp event must have an event rule");
        return parent::setRule($rule);
    }


    public function findRewardableUsers(Collection $users)
    {
        // TODO: Implement findRewardableUsers() method.
    }

    public function checkProgress(Rewardable $rewardable)
    {
        // TODO: Implement checkProgress() method.
    }

    public function getParticipatedUsers()
    {
        // TODO: Implement getParticipatedUsers() method.
    }

}