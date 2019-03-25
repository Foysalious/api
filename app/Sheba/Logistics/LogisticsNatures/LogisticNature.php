<?php
/**
 * Created by PhpStorm.
 * User: Irteza
 * Date: 3/13/2019
 * Time: 7:28 PM
 */

namespace Sheba\Logistics\LogisticsNatures;


use App\Models\Job;

abstract class LogisticNature
{
    protected $job;
    protected $orderKey;

    /**
     * @param Job $job
     * @return $this
     */
    public function setJob(Job $job)
    {
        $this->job = $job;
        return $this;
    }

    public function setOrderKey($key)
    {
        $this->orderKey = $key;
        return $this;
    }

    public function get()
    {
        return $this->getLogisticRouteInfo();
    }

    abstract public function getLogisticRouteInfo();
}