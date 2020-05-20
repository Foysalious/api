<?php namespace Sheba\Resource\Rewards;

use App\Models\Resource;
use App\Models\Reward;
use Sheba\Dal\RewardCampaignLog\RewardCampaignLog;

class RewardHistory
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
     * @return RewardHistory
     */
    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * @param int $limit
     * @return RewardHistory
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param int $offset
     * @return RewardHistory
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function get()
    {
        $reward_logs = [];

        dd($reward_logs);

        return $reward_logs;
    }
}