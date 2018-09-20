<?php namespace Sheba\Reward;

use App\Models\Reward;
use Sheba\Reward\Event\ActionRule;
use Sheba\Reward\Event\CampaignRule;
use Sheba\Reward\Event\Rule;

abstract class Event
{
    /** @var ShebaReward */
    protected $reward;
    /** @var ActionRule | CampaignRule */
    protected $rule;

    public function setRule(Rule $rule)
    {
        $this->rule = $rule;
        return $this;
    }

    public function setReward(Reward $reward)
    {
        $this->reward = $reward;
        return $this;
    }
}