<?php namespace Sheba\Resource\Rewards;

use App\Models\Resource;
use App\Models\Reward;
use Sheba\Reward\ResourceReward;

class RewardList
{
    private $limit;
    private $offset;
    /** @var Resource */
    private $resource;

    /**
     * RewardList constructor.
     */
    public function __construct()
    {
        $this->limit = 100;
        $this->offset = 0;
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
        $rewards = (new ResourceReward($this->resource))->upcoming();
        $campaigns = [];
        $actions = [];
        foreach ($rewards as $reward){
            if($reward->isCampaign()) array_push($campaigns, $this->formatRewardForRewardList($reward));
            else array_push($actions, $this->formatRewardForRewardList($reward));
        }
        return [
            'campaigns' => $campaigns,
            'actions' => $actions
        ];
    }

    public function formatRewardForRewardList(Reward $reward)
    {
        return [
            "id" => $reward['id'],
            "name" => $reward['name'],
            "short_description" => $reward['short_description'],
            "type" => $reward['type'],
            "amount" => $reward['amount'],
            "start_time" => $reward['start_time']->format('Y-m-d H:i:s'),
            "end_time" => $reward['end_time']->format('Y-m-d H:i:s'),
            "created_at" => $reward['created_at']->format('Y-m-d H:i:s'),
            "progress" => [
                "tag" => "order_serve",
                "is_completed" => 0,
                "target" => 5,
                "completed" => 2
            ]
        ];
    }
}