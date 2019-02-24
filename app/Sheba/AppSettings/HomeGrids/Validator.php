<?php namespace Sheba\AppSettings\HomeGrids;

use Illuminate\Support\Collection;

class Validator
{
    public function hasError(Collection $data)
    {
        if($this->hasSameSubItemTwice($data)) return "You can't use same Subitem twice.";
        if($msg = $this->notHasProperIcons($data)) return $msg . " does not have icon. Be sure to set them first properly.";
        return false;
    }

    private function hasSameSubItemTwice(Collection $data)
    {
        $model_counter = $data->count();
        $model_unique_counter = $data->unique()->count();
        return $model_counter != $model_unique_counter;
    }

    private function notHasProperIcons(Collection $data)
    {
        $error = false;
        $data->each(function ($item) use (&$error) {
            if(!$this->singleItemHasIcon($item)) {
                $error = $item['name'] . ": " . $item['_sub_item']['name'];
                return false;
            }
        });
        return $error;
    }

    private function singleItemHasIcon($item)
    {
        $grid_item_class = "App\\Models\\" . $item['name'];
        $grid_item = $grid_item_class::find($item['_sub_item']['id']);
        return !empty($grid_item->icon) && !empty($grid_item->icon_png);
    }
}
