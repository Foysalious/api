<?php namespace Sheba\Resource\Reward\Info;


use App\Models\Reward;
use Sheba\Reward\Rewardable;

abstract class Info
{
    /** @var Reward */
    protected $reward;
    /** @var Rewardable */
    protected $rewardable;

    public function setReward(Reward $reward)
    {
        $this->reward = $reward;
        return $this;
    }

    public function setRewardable(Rewardable $rewardable)
    {
        $this->rewardable = $rewardable;
        return $this;
    }

    public function getInfo()
    {
        return [
            "id" => $this->reward->id,
            "reward_type" => $this->reward->isCampaign() ? 'campaign' : 'action',
            "reward_type_id" => $this->reward->detail_id,
            "name" => $this->reward->name,
            "short_description" => $this->reward->short_description,
            "type" => $this->reward->type,
            "amount" => $this->reward->amount,
            "start_time" => $this->reward->start_time->toDateTimestring(),
            "end_time" => $this->reward->end_time->toDateTimestring(),
            "created_at" => $this->reward->created_at->toDateTimestring(),
            "rules" => $this->reward->getTerms(),
            'progress' => $this->getProgress()
        ];
    }

    abstract protected function getProgress();

}