<?php namespace Sheba\Resource\Rewards;

use App\Models\Resource;
use App\Models\Reward;
use Sheba\Dal\RewardCampaignLog\RewardCampaignLog;
use Sheba\Dal\RewardCampaignLog\RewardCampaignLogRepository;

class RewardHistory
{
    private $limit;
    private $offset;
    /** @var Resource */
    private $resource;
    private $reward_log_repo;

    /**
     * RewardList constructor.
     * @param  RewardCampaignLogRepository $reward_log_repository
     */
    public function __construct(RewardCampaignLogRepository $reward_log_repository)
    {
        $this->limit = 100;
        $this->offset = 0;
        $this->reward_log_repo = $reward_log_repository;
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
        $reward_logs = $this->reward_log_repo->getLogsForResource($this->resource->id);

        dd($reward_logs);

        return $reward_logs;
    }
}