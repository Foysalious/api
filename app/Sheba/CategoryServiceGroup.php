<?php namespace Sheba;

use App\Models\ServiceGroupService;

trait CategoryServiceGroup
{
    public function serviceGroupServiceIds()
    {
        $service_group_id = explode(',', config('sheba.service_group_ids'));
        $service_group_service_id = ServiceGroupService::whereIn('service_group_id', $service_group_id)->pluck('service_id')->toArray();
        return $service_group_service_id;
    }
}
