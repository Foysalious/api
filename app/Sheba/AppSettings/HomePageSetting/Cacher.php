<?php namespace Sheba\AppSettings\HomePageSetting;

use App\Models\CategoryGroup;
use App\Models\GridPortal;
use App\Models\OfferShowcase;
use App\Models\ScreenSetting;
use App\Models\SliderPortal;
use App\Models\Voucher;
use Sheba\AppSettings\HomePageSetting\Getters\Getter as HomePageSettingGetter;
use Cache;
use Illuminate\Support\Collection;

class Cacher
{
    private $memory;
    private $redisNameSpace = 'NewScreenSetting';
    private $getter;

    public function __construct(HomePageSettingGetter $getter)
    {
        $this->memory = [
            'slider_portal' => collect(),
            'slider_slide' => collect(),
            'grid_portal' => collect(),
            'grid_block' => collect(),
            'category_group' => collect(),
            'offer' => collect(),
            'voucher' => collect(),
            'element' => collect()
        ];
        $this->getter=$getter;
    }

    public function update()
    {
        /** @var \Illuminate\Contracts\Cache\Repository $store */
        $store = Cache::store('redis');
        $settings = $this->getHomeSettings();
        $i = 0;
        foreach ($settings as $setting) {
            $elements = $setting->elements;
//            $this->saveElements($elements);
            $location_ids = $elements->pluck('pivot.location_id')->unique();
            foreach ($location_ids as $location_id) {
//                $elements_of_that_location = $elements->where('pivot.location_id', $location_id);
//                $final = collect();
//                foreach ($elements_of_that_location as $element_of_that_location) {
//                    $i++;
//                    $element_of_that_location = $this->memory['element']->where('id', $element_of_that_location->id)->first();
//                    $type = class_basename($element_of_that_location->item_type);
//                    $data_method = "get" . $type . "Data";
//                    $data = $this->$data_method($element_of_that_location->item_id, $location_id, $setting);
//                    if (empty($data)) continue;
//                    $final->push([
//                        'item_type' => $type == 'Grid' ? $element_of_that_location->item->getSettingsName() : $type,
//                        'item_id' => $element_of_that_location->item_id,
//                        'name' => $type == "CategoryGroup" ? $element_of_that_location->item->name : null,
//                        'order' => $element_of_that_location->pivot->order,
//                        'updated_at' => $setting->updated_at->toDateTimeString(),
//                        'updated_at_timestamp' => $setting->updated_at->timestamp,
//                        'data' => $data,
//                    ]);
//                }
                $home_page_settings=$this->getter->setLocation($location_id)->setPortal($setting->portal_name)->setScreen($setting->screen)->getSettings()->toJson();
                $portal_name = snake_case(camel_case($setting->portal_name));
                $cache_name = sprintf("%s::%s_%s_%d", $this->redisNameSpace, $portal_name, $setting->screen, $location_id);
                $store->forever($cache_name, $home_page_settings);
            }
        }
    }

    private function getHomeSettings()
    {
        return ScreenSetting::with('elements')->get();
    }

    public function getSliderData($slider_id, $location_id, ScreenSetting $setting)
    {
        $slider_portal = $this->memory['slider_portal']->where('slider_id', $slider_id)->where('portal_name', $setting->portal_name)->first();
        if (!$slider_portal) $slider_portal = SliderPortal::where([['slider_id', $slider_id], ['portal_name', $setting->portal_name]])->first();
        $slider = $slides = '';
        $slider = $slider_portal->slider;
        $slides = $slider->slides;
        $slides = $slides->where('pivot.location_id', $location_id);
        $final = $slides->map(function ($slide) {
            $target_type = class_basename($slide->target_type);
            $image = $this->memory['slider_slide']->where('id', (int)($slide->id))->first();
            if ($image) return $image;
            $data = [
                "small_image_link" => $slide->small_image_link,
                "image_link" => $slide->image_link,
                'id' => $slide->id,
                "target_type" => $target_type ?: null,
                "target_id" => $target_type == "Voucher" ? Voucher::find($slide->target_id)->code : (int)$slide->target_id ?: null,
                "target_link" => empty($slide->target_type) ? $slide->target_link : null,
                "is_parent" => $target_type == "Category" ? is_null($slide->target->parent_id) : null,
                "link" => $target_type == "ExternalProject" ? $slide->target->app_link : null,
                'order' => $slide->pivot->order,
                "updated_at" => $slide->updated_at->toDateTimeString(),
                "updated_at_timestamp" => $slide->updated_at->timestamp
            ];
            $this->memory['slider_slide']->push($data);
            return $data;
        })->sortBy('order')->values()->toArray();
        $this->memory['slider_portal']->push($slider_portal);

        return $final;
    }

    public function getGridData($grid_id, $location_id, ScreenSetting $setting)
    {
        $grid_portal = $this->memory['grid_portal']->where('grid_id', $grid_id)->where('portal_name', $setting->portal_name)->first();
        if (!$grid_portal) $grid_portal = GridPortal::where([['grid_id', $grid_id], ['portal_name', $setting->portal_name]])->with('grid.blocks')->first();
        $grid_data = $grid_portal->grid->blocks->where('pivot.location_id', $location_id);
        $final = $grid_data->map(function ($grid_item) {
            $type = class_basename($grid_item->item_type);
            $block = $this->memory['grid_block']->where('item_id', (int)$grid_item->item_id)->where('item_type', $type)->first();
            if ($block) return $block;
            $data = [
                "name" => $grid_item->item->name,
                "icon" => $grid_item->item->icon,
                "icon_png" => $grid_item->item->icon_png,
                "item_type" => $type,
                "item_id" => (int)$grid_item->item_id,
                "is_parent" => $type == "Category" ? is_null($grid_item->item->parent_id) : null,
                "link" => $type == "ExternalProject" ? $grid_item->item->app_link : null,
                'order' => $grid_item->pivot->order,
                "updated_at" => $grid_item->updated_at->toDateTimeString(),
                "updated_at_timestamp" => $grid_item->updated_at->timestamp
            ];
            $this->memory['grid_block']->push($data);
            return $data;
        })->sortBy('order')->values()->toArray();
        $this->memory['grid_portal']->push($grid_portal);
        return $final;
    }

    public function getCategoryGroupData($id)
    {
        /** @var CategoryGroup $group */
        $group = $this->memory['category_group']->where('id', $id)->first();
        if (!$group) {
            $group = CategoryGroup::find($id);
            $group->load(['categories' => function ($q) {
                $q->published();
            }]);
        }
        if (!$group->is_published_for_app) return null;
        $categories = $group->categories;
        $this->memory['category_group']->push($group);
        $data = $categories->map(function ($category) {
            return [
                "id" => $category->id,
                "name" => $category->name,
                "slug" => $category->slug,
                "thumb" => $category->app_thumb,
                "icon_png" => $category->icon_png,
                "is_parent" => is_null($category->parent_id),
                "updated_at" => $category->updated_at->toDateTimeString(),
                "updated_at_timestamp" => $category->updated_at->timestamp
            ];
        });
        return $data->values()->toArray();
    }

    public function getOfferShowcaseData($id)
    {
        /** @var OfferShowcase $offer */
        $offer = $this->memory['offer']->where('id', $id)->first();
        if (!$offer) $offer = OfferShowcase::find($id);
        if (!$offer->is_active || !$offer->isInValidationTime()) {
            $this->memory['offer']->push($offer);
            return null;
        }
        $target_type = class_basename($offer->target_type);
        if ($target_type == 'Category' && !$offer->target->publication_status) return null;
        if ($target_type == 'CategoryGroup' && !$offer->target->is_published_for_app) return null;
        if ($target_type == 'Voucher' && !$offer->target->isValid()) return null;
        $data[] = [
            'id' => $offer->id,
            'name' => $offer->name,
            'banner' => $offer->app_banner,
            "is_flash" => $offer->is_flash,
            "valid_till" => $offer->end_date->toDateTimeString(),
            "target_type" => $target_type ?: null,
            "target_id" => $target_type == "Voucher" ? ($this->getVoucher($offer->target_id))->code : (int)$offer->target_id ?: null,
            "target_link" => empty($offer->target_type) ? $offer->target_link : null,
            "is_parent" => $target_type == "Category" ? is_null($offer->target->parent_id) : null,
            "link" => $target_type == "ExternalProject" ? $offer->target->app_link : null,
            "updated_at" => $offer->updated_at->toDateTimeString(),
            "updated_at_timestamp" => $offer->updated_at->timestamp
        ];
        $this->memory['offer']->push($offer);
        return $data;
    }
    public function getHomeMenuData($id,$location_id,$setting){
        return [];
    }

    /**
     * @param $id
     * @return Voucher
     */
    private function getVoucher($id)
    {
        $voucher = $this->memory['voucher']->where('id', $id)->first();
        if (!$voucher) $voucher = Voucher::find($id);
        return $voucher;
    }

    /**
     * @param Collection $elements
     */
    private function saveElements(Collection $elements)
    {
        $item_types = $elements->groupBy('item_type');
        foreach ($item_types as $item_type) {
            foreach ($item_type->unique('id') as $item) {
                $elem = $this->memory['element']->where('id', $item->id)->first();
                if (!$elem) $this->memory['element']->push($item->load('item'));
            }
        }
    }
}
