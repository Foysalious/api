<?php namespace App\Sheba\AppSettings\HomePageSetting\Getters;

use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\Grid;
use App\Models\HomeMenu;
use App\Models\OfferGroup;
use App\Models\OfferShowcase;
use App\Models\ScreenSetting;
use App\Models\Slider;
use Sheba\AppSettings\HomePageSetting\DS\Setting;
use Sheba\AppSettings\HomePageSetting\Getters\Getter;

class HomePage extends Getter
{
    private $screenSettings;

    public function getSettings(): Setting
    {
        $setting = new Setting();
        if (!$this->setScreenSettings()) return $setting;
        foreach ($this->screenSettings->elements as $element) {
            $type = class_basename($element->item_type);
            $data_method = $data_method = "get" . $type . "Data";
            $data = $this->$data_method($element->item_id, $this->getLocation());
            if ($data) $setting->push($data);
        }
        return $setting;
    }

    /**
     * @return bool
     */
    public function setScreenSettings()
    {
        if (empty($this->getPortal()) || empty($this->getScreen()) || empty($this->getLocation())) return false;
        $location_ids = $this->getLocation();
        $this->screenSettings = ScreenSetting::where(['portal_name' => $this->getPortal(), 'screen' => $this->getScreen()])
            ->with(['elements' => function ($q) use ($location_ids) {
                $q->where('location_id', $location_ids)->orderBy('pivot_order');
            }])->whereHas('elements', function ($q) use ($location_ids) {
                $q->where('location_id', $location_ids);
            })->first();
        if (!$this->screenSettings) return false;
        return true;
    }

    private function getHomeMenuData($id, $location_id)
    {
        $menu = HomeMenu::where(['id' => $id])->whereHas('locations', function ($q) use ($location_id) {
            $q->where('id', $location_id);
        })->first();
        if (!$menu) return null;
        return $this->sectionBuilder->buildMenu($menu, $location_id);
    }

    private function getOfferShowcaseData($id, $location_id)
    {
        $offer = OfferShowcase::query()->active()->where('id', $id)->whereHas('locations', function ($q) use ($location_id) {
            $q->where('id', $location_id);
        })->first();
        if (!$offer) return null;

        return $this->sectionBuilder->buildBanner($offer);
    }

    private function getSliderData($id, $location_id)
    {
        $portal = $this->getPortal();
        $screen = $this->getScreen();
        $slider = Slider::query()->where('id', $id)->published()->whereHas('sliderPortal', function ($q) use ($portal, $screen) {
            $q->where('portal_name', $portal)->where('screen', $screen);
        })->with(['slides' => function ($q) use ($location_id) {
            $q->where('location_id', $location_id);
        }])->first();
        if (!$slider) return null;
        return $this->sectionBuilder->buildSlider($slider, $location_id);
    }

    private function getOfferGroupData($id, $location_id)
    {
        $offerGroup = OfferGroup::query()->where('id', $id)->whereHas('locations', function ($q) use ($location_id) {
            $q->where('id', $location_id);
        })->first();
        if (!$offerGroup) return null;
        return $this->sectionBuilder->buildOfferGroup($offerGroup);
    }

    private function getCategoryData($id, $location_id)
    {
        return $this->sectionBuilder->buildCategories($location_id);
    }

    private function getGridData($id, $location_id)
    {
        $portal = $this->getPortal();
        $screen = $this->getScreen();
        $grid = Grid::query()->published()->where('id', $id)->whereHas('portals', function ($q) use ($portal, $screen) {
            $q->where('portal_name', $portal)->where('screen', $screen);
        })->with(['blocks' => function ($q) use ($location_id) {
            return $q->where('location_id', $location_id)->orderBy('order', 'asc');
        }])->first();
        if (!$grid) return null;
        return $this->sectionBuilder->buildGrid($grid, $location_id);
    }

    private function getOfferListData($id, $location_id)
    {
        return $this->sectionBuilder->buildOfferList($location_id);
    }

    private function getSubscriptionOrderData($id, $location_id)
    {
        return $this->sectionBuilder->buildSubscriptionList($location_id);
    }

    private function getCategoryGroupData($id, $location_id)
    {
        $category_group = CategoryGroup::query()->where('id', $id)->whereHas('locations', function ($q) use ($location_id) {
            return $q->where('id', $location_id);
        })->first();
        if (!$category_group) return null;
        return $this->sectionBuilder->buildCategoryGroup($category_group);
    }

    private function getTopUpOrderData()
    {
        return $this->sectionBuilder->buildTopUp();
    }
}
