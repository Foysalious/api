<?php namespace Sheba\Reward\Event;


use App\Models\Customer;
use App\Models\Partner;
use App\Models\Resource;
use Exception;
use Sheba\Dal\src\RewardCampaignLog\Types;
use Sheba\Reward\Rewardable;

class ParticipatedCampaignUser
{
    /** @var Rewardable */
    private $user;
    private $achievedValue;
    private $isTargetAchieved;

    public function setIsTargetAchieved($is_target_achieved)
    {
        $this->isTargetAchieved = $is_target_achieved;
        return $this;
    }

    /**
     * @param Rewardable $user
     * @return ParticipatedCampaignUser
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param mixed $achievedValue
     * @return ParticipatedCampaignUser
     */
    public function setAchievedValue($achievedValue)
    {
        $this->achievedValue = $achievedValue;
        return $this;
    }

    /**
     * @return Rewardable
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return mixed
     */
    public function getAchievedValue()
    {
        return $this->achievedValue;
    }

    /**
     * @return mixed
     */
    public function getIsTargetAchieved()
    {
        if ($this->isTargetAchieved === null) throw new Exception('Target achieve not set');
        return $this->isTargetAchieved;
    }

    public function getUserType()
    {
        if ($this->user instanceof Partner) return Types::PARTNER;
        elseif ($this->user instanceof Resource) return Types::RESOURCE;
        elseif ($this->user instanceof Customer) return Types::CUSTOMER;
    }
}