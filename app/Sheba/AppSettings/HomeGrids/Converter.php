<?php namespace Sheba\AppSettings\HomeGrids;

use Illuminate\Support\Collection;

class Converter
{
    public function tableDataToFormData(Collection $data)
    {
        $data = $data->map(function ($item) {
            return [
                'name' => substr($item->item_type,11),
                '_sub_item' => [
                    'id' => $item->item_id,
                    'name' => $item->item->name
                ]
            ];
        });
        $data = $data->count() ? $data : [];
        return json_encode($data);
    }
}