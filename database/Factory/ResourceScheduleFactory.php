<?php namespace Factory;


use App\Models\ResourceSchedule;
use Carbon\Carbon;

class ResourceScheduleFactory extends Factory
{

    protected function getModelClass()
    {
        return ResourceSchedule::class;// TODO: Implement getModelClass() method.
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'id' => 1,
            'resource_id' => 1,
            'job_id' => 1,
            'start' => Carbon::now(),
            'end' => Carbon::now(),
            'notify_at' => Carbon::now()
        ]);// TODO: Implement getData() method.
    }
}
