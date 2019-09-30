<?php

use App\Models\Partner;
use App\Models\Resource;

use Sheba\Repositories\ResourceScheduleRepository;
use Sheba\ResourceScheduler\PartnerHandler;
use Sheba\ResourceScheduler\ResourceHandler;

if(!function_exists('scheduler')) {
    /**
     * @param $model
     * @return PartnerHandler|ResourceHandler
     */
    function scheduler($model)
    {
        if ($model instanceof Resource) {
            return new ResourceHandler(app(ResourceScheduleRepository::class), $model);
        } elseif ($model instanceof Partner) {
            return (new PartnerHandler($model));
        }
    }
}