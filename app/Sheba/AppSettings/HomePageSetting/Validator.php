<?php namespace Sheba\AppSettings\HomePageSetting;

use App\Models\Location;
use App\Models\OfferShowcase;
use App\Models\Slider;
use Illuminate\Support\Collection;

class Validator
{
    private $location;

    public function setLocation($city)
    {
        $this->location = Location::where('city_id', $city)->published()->first();
        return $this;
    }

    public function hasError(Collection $data)
    {
        if ($this->hasTwoSliders($data)) return "You can't use multiple slider.";
        if ($this->hasTwoGrids($data)) return "You can't use multiple grid.";
        if ($this->hasSameCategoryGroupTwice($data)) return "You can't use same category group twice.";
        if ($this->hasSameOfferTwice($data)) return "You can't use same offer twice.";
        if ($msg = $this->notHasProperImages($data)) return $msg . "Be sure to set them first properly.";
        return false;
    }

    private function hasTwoSliders($data)
    {
        return $this->hasSameItemTwice($data, "App\\Models\\Slider");
    }

    private function hasTwoGrids($data)
    {
        return $this->hasSameItemTwice($data, "App\\Models\\HomeGrid");
    }

    private function hasSameItemTwice($data, $model)
    {
        return $this->getOnlySingleModelData($data, $model)->count() > 1;
    }

    private function getOnlySingleModelData($data, $model)
    {
        return $data->filter(function ($item) use ($model) {
            return $item['item_type'] == $model;
        });
    }

    private function hasSameCategoryGroupTwice($data)
    {
        return $this->hasSameSubItemTwice($data, "App\\Models\\CategoryGroup");
    }

    private function hasSameOfferTwice($data)
    {
        return $this->hasSameSubItemTwice($data, "App\\Models\\OfferShowcase");
    }

    private function hasSameSubItemTwice($data, $model)
    {
        $model_filtered = $this->getOnlySingleModelData($data, $model);
        $model_counter = $model_filtered->count();
        $model_unique_counter = $model_filtered->unique('item_id')->count();
        return $model_counter != $model_unique_counter;
    }

    private function notHasProperImages(Collection $data)
    {
        $error = false;
        $data->each(function ($item) use (&$error) {
            if ($item['item_type'] == "App\\Models\\Slider") {
                if (!$this->sliderHasProperImage($item['item_id'])) {
                    $error = "All sliders does not have small image set up. ";
                    return false;
                }
            } else if ($item['item_type'] == "App\\Models\\OfferShowcase") {
                if (!$this->offerHasProperImage($item['item_id'])) {
                    $error = "Used offers does not have app banner set up. ";
                    return false;
                }
            }
        });

        return $error;
    }

    /**
     * @param $slider_id
     * @return bool
     */
    private function sliderHasProperImage($slider_id)
    {
        $slider = Slider::with(['slides' => function ($q) {
            $q->where('location_id', $this->location->id);
        }])->where('id', $slider_id)->first();

        $slides = $slider->slides->pluck('small_image_link');
        return $slides->count() == $slides->filter()->count();
    }

    private function offerHasProperImage($offer_id)
    {
        $offer = OfferShowcase::find($offer_id);
        return !empty($offer->app_banner);
    }
}