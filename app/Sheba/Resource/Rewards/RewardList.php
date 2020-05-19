<?php namespace Sheba\Resource\Rewards;

use App\Models\Resource;
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
        $rewards = [];
        $rewards = (new ResourceReward($this->resource))->upcoming();
        return $rewards;
    }
}