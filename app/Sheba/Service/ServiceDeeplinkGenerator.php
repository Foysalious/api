<?php

namespace Sheba\Service;

use Sheba\Dal\Service\Service;

class ServiceDeeplinkGenerator
{
    public function getDeeplink(Service $service, $withZxing = true)
    {
        $zxing_text = 'zxing://';
        $ios = "host_sub-category_service_detail/?url=".config('sheba.front_url')."/service-details/".$service->id ."?category_id=".$service->category->id;
        $android = "host_sub-category_service_detail/?url=".config('sheba.front_url')."/service-details/".$service->id ."?category_id=".$service->category->id;
        return [
            'android' => $withZxing ? $zxing_text.$android : $android,
            'ios' => $withZxing ? $zxing_text.$ios : $ios,
            'sub_link' => "_service_detail/?url=".config('sheba.front_url')."/service-details/".$service->id ."?category_id=".$service->category->id
        ];
    }

}