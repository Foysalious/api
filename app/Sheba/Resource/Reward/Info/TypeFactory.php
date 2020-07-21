<?php namespace Sheba\Resource\Reward\Info;

use App\Models\Reward;

class TypeFactory
{
    /** @var Reward */
    protected $reward;

    public function setReward(Reward $reward)
    {
        $this->reward = $reward;
        return $this;
    }

    /**
     * @return Info
     */
    public function getType()
    {
        /** @var Info $info */
        $info = $this->reward->isCampaign() ? app(Campaign::class) : app(Action::class);
        $info->setReward($this->reward);
        return $info;
    }
}