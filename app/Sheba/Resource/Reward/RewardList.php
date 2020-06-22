<?php namespace Sheba\Resource\Reward;

use App\Models\Resource;
use Sheba\Resource\Reward\Info\TypeFactory;
use Sheba\Reward\ResourceReward;

class RewardList
{
    private $limit;
    private $offset;
    /** @var Resource */
    private $resource;
    private $resource_reward;
    private $typeFactory;


    public function __construct(ResourceReward $resource_reward, TypeFactory $typeFactory)
    {
        $this->limit = 100;
        $this->offset = 0;
        $this->resource_reward = $resource_reward;
        $this->typeFactory = $typeFactory;
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

    public function getCampaigns()
    {
        return $this->getByType('campaign');
    }

    public function getActions()
    {
        return $this->getByType('action');
    }

    private function getByType($type)
    {
        $rewards = $this->resource_reward->setOffset($this->offset)->setLimit($this->limit)->setType($type)->upcoming();
        $data = [];
        foreach ($rewards as $reward) {
            $type = $this->typeFactory->setReward($reward)->getType();
            array_push($data, $type->setRewardable($this->resource)->getInfo());
        }
        return $data;
    }
}