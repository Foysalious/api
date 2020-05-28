<?php namespace Sheba\Resource\Reward;

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

    public function formatRewardLog(RewardCampaignLog $log)
    {
        $reward = $log->rewardCampaign->reward;
        $events = $log->rewardCampaign->getEvents();
        $target = reset($events)['target'];
        return [
            "id" => $log->id,
            "log" => $reward ? $reward->short_description : 'N/A',
            "created_at" => $log->created_at,
            "reward" => $reward ? [
                "id" => $reward->id,
                "name" => $reward->name,
                "type" => $reward->type,
                "amount" => $reward->amount
            ] : null,
            "progress" => [
                "is_completed" => $log->achieved >= $target ? 1 : 0,
                "target" => $target,
                "completed" => $log->achieved
            ]
        ];
    }

    public function get()
    {
        $logs = $this->reward_log_repo->getLogsForResource($this->resource->id)->get();
        $formatted_logs = [];
        foreach ($logs as $log){
            array_push($formatted_logs, $this->formatRewardLog($log));
        }
        return $formatted_logs;
    }
}