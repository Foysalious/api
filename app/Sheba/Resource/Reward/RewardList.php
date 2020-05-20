<?php namespace Sheba\Resource\Reward;

use App\Models\Resource;
use App\Models\Reward;
use Sheba\Reward\CampaignEventInitiator;
use Sheba\Reward\ResourceReward;

class RewardList
{
    private $limit;
    private $offset;
    /** @var Resource */
    private $resource;
    private $resource_reward;
    private $eventInitiator;


    public function __construct(ResourceReward $resource_reward, CampaignEventInitiator $event_initiator)
    {
        $this->limit = 100;
        $this->offset = 0;
        $this->resource_reward = $resource_reward;
        $this->eventInitiator = $event_initiator;
    }


    /**
     * @param Resource $resource
     * @return RewardList
     */
    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * @param int $limit
     * @return RewardList
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param int $offset
     * @return RewardList
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function get()
    {
        $rewards = $this->resource_reward->setOffset($this->offset)->setLimit($this->limit)->upcoming();
        $campaigns = [];
        $actions = [];
        foreach ($rewards as $reward) {
            if ($reward->isCampaign()) array_push($campaigns, $this->formatRewardForRewardList($reward));
            else array_push($actions, $this->formatRewardForRewardList($reward));
        }
        return [
            'campaigns' => $campaigns,
            'actions' => $actions
        ];
    }

    public function getCampaigns()
    {
        $rewards = $this->resource_reward
            ->setOffset($this->offset)
            ->setLimit($this->limit)
            ->setType('campaign')
            ->upcoming();
        $campaigns = [];

        foreach ($rewards as $reward) {
            array_push($campaigns, $this->formatRewardForRewardList($reward, true));
        }
        return $campaigns;
    }

    public function getActions()
    {
        $rewards = $this->resource_reward
            ->setOffset($this->offset)
            ->setLimit($this->limit)
            ->setType('action')
            ->upcoming();
        $actions = [];

        foreach ($rewards as $reward) {
            array_push($actions, $this->formatRewardForRewardList($reward));
        }
        return $actions;
    }

    public function formatRewardForRewardList(Reward $reward, $has_progress = false)
    {
        $progress = [];
        $data = [
            "id" => $reward['id'],
            "name" => $reward['name'],
            "short_description" => $reward['short_description'],
            "type" => $reward['type'],
            "amount" => $reward['amount'],
            "start_time" => $reward['start_time']->format('Y-m-d H:i:s'),
            "end_time" => $reward['end_time']->format('Y-m-d H:i:s'),
            "created_at" => $reward['created_at']->format('Y-m-d H:i:s'),
        ];
        if ($reward->isCampaign()) {
            foreach (json_decode($reward->detail->events) as $key => $event) {
                $event = $this->eventInitiator->setReward($reward)->setName($key)->setRule($event)->initiate();
                $target_progress = $event->checkProgress($this->resource);
                array_push($progress, array(
                    'tag' => $key,
                    'target' => $target_progress->getTarget(),
                    'completed' => $target_progress->getAchieved(),
                    'is_completed' => $target_progress->hasAchieved() ? 1 : 0
                ));
            }
        }
        $data['progress'] = $progress;
        return $data;
    }
}