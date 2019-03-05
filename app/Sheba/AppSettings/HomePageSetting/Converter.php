<?php namespace Sheba\AppSettings\HomePageSetting;

use Illuminate\Support\Collection;

class Converter
{
    private $availableItems;

    public function __construct(AvailableItems $items)
    {
        $this->availableItems = $items;
    }

    public function formDataToTableData($form_data)
    {
        $form_data = json_decode($form_data, true);
        $data = [];
        foreach ($form_data as $key => $item) {
            $data[] = [
                'order' => $key + 1,
                'item_type' => $this->availableItems->getModel($item['key']),
                'item_id' => $item['subItem'] ? $item['subItem']['id'] : null,
            ];
        }
        return $data;
    }

    public function tableDataToFormData(Collection $data)
    {
        $data = $data->map(function ($item) {
            return [
                'key' => $key = $this->availableItems->getKeyByModel($item->item_type),
                'name' => $this->availableItems->getName($key),
                'subItem' => !($item->item_id) ? null : [
                    'id' => $item->item_id,
                    'name' => $item->item->name
                ]
            ];
        });
        $data = $data->count() ? $data : [];
        return json_encode($data);
    }
}