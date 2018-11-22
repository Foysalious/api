<?php

namespace App\Transformers;


use League\Fractal\Serializer\DataArraySerializer;

class CustomSerializer extends DataArraySerializer
{
    public function mergeIncludes($transformedData, $includedData)
    {
        $includedData = array_map(function ($include) {
            return $include['data'];
        }, $includedData);

        return parent::mergeIncludes($transformedData, $includedData);
    }
}