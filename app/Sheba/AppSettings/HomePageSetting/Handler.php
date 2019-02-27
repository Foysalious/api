<?php namespace Sheba\AppSettings\HomePageSetting;

use App\Models\Location;
use App\Models\ScreenSetting;
use App\Models\ScreenSettingElement;

use DB;
use Sheba\ModificationFields;

class Handler
{
    use ModificationFields;

    private $cacher;
    private $location;
    private $title;
    private $screen;
    private $portal;
    private $screenSetting;

    public function __construct(Cacher $cacher)
    {
        $this->cacher = $cacher;
    }

    public function setLocation($city)
    {
        $this->location = Location::where('city_id', $city)->published()->get();
        return $this;
    }

    public function setScreenSetting($screen_setting)
    {
        $this->screenSetting = $screen_setting;
        return $this;
    }

    public function save($data)
    {
        $this->screenSetting->elements()->detach();
        foreach ($data as $element) {
            if ($this->isCurrentSettingExists($element['item_type'], $element['item_id'])) {
                $screen_setting_element = ScreenSettingElement::where('item_type', $element['item_type'])->where('item_id', $element['item_id'])->first();
            } else {
                $screen_setting_element = ScreenSettingElement::create($this->withBothModificationFields([
                    'item_type' => $element['item_type'],
                    'item_id' => $element['item_id'] ?: 0,
                ]));
            }

            $this->locationTagWithScreenSettingElement($screen_setting_element, $element['order']);
        }

        $this->cacher->update();
    }

    private function isCurrentSettingExists($item_type, $item_id)
    {
        return DB::table('screen_setting_elements')->where('item_type', $item_type)->where('item_id', $item_id)->exists();
    }

    private function locationTagWithScreenSettingElement($Screen_setting_element, $order)
    {
        $temp = [];
        $this->location->each(function ($location) use (&$temp, $Screen_setting_element, $order) {
            $temp[$location->id] = [
                'location_id' => $location->id,
                'screen_setting_id' => $this->screenSetting->id,
                'screen_setting_element_id' => $Screen_setting_element->id,
                'order' => $order,
            ];
        });
        DB::table('location_screen_setting')->insert($temp);
    }

    /**
     * PREVIOUSLY USE THIS METHOD -- REMOVE LATER
     *
     * public function save($data)
     * {
     * $current_settings_screen = ScreenSetting::with(['elements' => function($q) {
     * $q->where('location_id', $this->location->first()->id);
     * }])->where('portal_name', $this->portal)
     * ->where('screen', $this->screen)
     * ->first();
     *
     * if ($current_settings_screen) {
     * $created_counter = 0;
     * $updated_counter = 0;
     * $deleted_counter = 0;
     * $current_settings = $current_settings_screen->elements;
     *
     * foreach ($data as $item) {
     * $current_setting_key = $item['order'] - 1;
     *
     * if (!$current_settings->has($current_setting_key)) {
     * $Screen_setting_element = ScreenSettingElement::create($this->withBothModificationFields($item));
     * $created_counter++;
     * $this->locationTagWithScreenSettingElement($current_settings_screen, $Screen_setting_element, $item['order']);
     * continue;
     * }
     *
     * if ($this->isCurrentSettingUpdated($current_settings[$current_setting_key], $item)) {
     * $current_settings[$current_setting_key]->update($this->withUpdateModificationField($item));
     * $updated_counter++;
     * }
     * }
     *
     * $new_data_orders = collect($data)->map(function ($item) {
     * return $item['order'];
     * });
     *
     * foreach ($current_settings as $current_setting) {
     * if(!$new_data_orders->contains($current_setting->order)) {
     * $current_setting->delete();
     * $deleted_counter++;
     * }
     * }
     *
     * $this->cacher->update();
     * }
     *
     * return "$created_counter items created & $updated_counter items updated & $deleted_counter items deleted.";
     * }*/

    /**
     * PREVIOUSLY USE THIS METHOD -- REMOVE LATER
     *
     * private function isCurrentSettingUpdated($current_setting, $new_data)
     * {
     * return $current_setting->item_type != $new_data['item_type'] || $current_setting->item_id != $new_data['item_id'];
     * }*/
}
