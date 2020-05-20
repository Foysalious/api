<?php namespace Sheba\Resource\Reward\Info;


use App\Models\Reward;

abstract class Info
{
    protected $reward;

    public function setReward(Reward $reward)
    {
        $this->reward = $reward;
        return $this;
    }

    abstract public function get();

}