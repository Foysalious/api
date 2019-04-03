<?php namespace Sheba\AppSettings\HomePageSetting\DS\Builders;

use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\Grid;
use App\Models\HomeMenu;
use App\Models\OfferGroup;
use App\Models\OfferShowcase;
use App\Models\ServiceSubscription;
use App\Models\Slider;
use Carbon\Carbon;
use Sheba\AppSettings\HomePageSetting\DS\Item;
use Sheba\AppSettings\HomePageSetting\DS\ItemSet;
use Sheba\AppSettings\HomePageSetting\DS\Section;
use Sheba\AppSettings\HomePageSetting\Supported\Sections;
use Sheba\AppSettings\HomePageSetting\Supported\Targets;

class SectionBuilder
{
    private $mockUpdatedAt, $mockHeight;
    private $itemBuilder;

    public function __construct(ItemBuilder $builder)
    {
        $this->mockUpdatedAt = Carbon::parse('2019-01-01');
        $this->itemBuilder = $builder;
        $this->mockHeight = 300;
    }

    public function buildMenu(HomeMenu $menu, $location_id)
    {
        $section = (new Section())->setType(Sections::MENU)->setName("Menu")->setUpdatedAt($this->mockUpdatedAt);
        $items = new ItemSet();
        foreach ($menu->elements as $element) {
            $item = $this->itemBuilder->buildMenu($element, $location_id);
            if ($item) $items->push($item);
        }
        return $this->setItems($section, $items);
    }

    public function buildCategories($location_id)
    {
        $masterCategories = Category::query()->parents()->whereHas('children', function ($q) use ($location_id) {
            $q->whereHas('services', function ($qa) use ($location_id) {
                $qa->published()->whereHas('locations', function ($query) use ($location_id) {
                    $query->where('id', $location_id);
                });
            })->whereHas('locations', function ($qa) use ($location_id) {
                $qa->where('id', $location_id);
            })->published();
        })->with(['children' => function ($q) use ($location_id) {
            $q->whereHas('services', function ($qa) use ($location_id) {
                $qa->published()->whereHas('locations', function ($query) use ($location_id) {
                    $query->where('id', $location_id);
                });
            })->whereHas('locations', function ($qa) use ($location_id) {
                $qa->where('id', $location_id);
            })->published()->orderBy('order');
        }])->published()->orderBy('order')->get();
        $categories = new ItemSet();
        $masterCategories=$masterCategories->filter(function ($category) {
            return $category->children->count() > 0;
        });
        foreach ($masterCategories as $category) {
            $item = $this->itemBuilder->buildCategory($category);
            $children = [];
            foreach ($category->children as $child) {
                $children[] = $this->itemBuilder->buildCategory($child)->toArray();
            }
            if (count($children) > 1) $item->setChildren($children);
            $categories->push($item);
        }
        $section = (new Section())->setType(Sections::MASTER_CATEGORIES)->setName("Our Categories")->setUpdatedAt($this->mockUpdatedAt);
        return (!$categories->isEmpty()) ? $this->setItems($section, $categories) : null;
    }

    public function buildBanner(OfferShowcase $offer)
    {
        $item = $this->itemBuilder->buildOffer($offer);
        if (!$item) return null;
        $items = (new ItemSet())->push($item);
        $section = (new Section())->setType(Sections::BANNER)->setHeight($offer->getHeight())->setRatio($offer->getRatio())->setUpdatedAt($offer->updated_at ?: $this->mockUpdatedAt);
        return $this->setItems($section, $items);
    }

    public function buildSubscriptionList($location_id)
    {
        $subscriptions = ServiceSubscription::whereHas('service', function ($q) use ($location_id) {
            $q->whereHas('locations', function ($qa) use ($location_id) {
                $qa->where('id', $location_id);
            });
        })->get();
        return !($subscriptions->isEmpty()) ? (new Section())->setType(Sections::SUBSCRIPTION_LIST)->setUpdatedAt($this->mockUpdatedAt) : null;
    }

    public function buildOfferList($location_id)
    {
        return (new Section())->setType(Sections::OFFER_LIST)->setUpdatedAt($this->mockUpdatedAt);

    }

    public function buildSlider(Slider $slider)
    {
        $item_set = new ItemSet();
        foreach ($slider->slides as $slide) {
            $item = $this->itemBuilder->buildSlide($slide);
            if (!empty($item)) {
                $item_set->push($item);
            }
        }
        $section = (new Section())->setType(Sections::SLIDER)->setUpdatedAt($this->mockUpdatedAt);
        return (!$item_set->isEmpty()) ? $this->setItems($section, $item_set) : null;
    }

    public function buildCategoryGroup(CategoryGroup $category_group)
    {
        $categories = new ItemSet();
        $section = (new Section())->setName($category_group->name)->setId($category_group->id)
            ->setType(Sections::CATEGORY_GROUP)->setUpdatedAt($this->mockUpdatedAt);
        foreach ($category_group->categories as $category) {
            $categories->push($this->itemBuilder->buildCategory($category));
        }
        return !$categories->isEmpty() ? $this->setItems($section, $categories) : null;
    }

    public function buildTopUp()
    {
        return (new Section())->setType(Sections::TOPUP)->setUpdatedAt($this->mockUpdatedAt);
    }

    public function buildOfferGroup(OfferGroup $offerGroup)
    {
        $item_set = new ItemSet();
        foreach ($offerGroup->offers as $offer) {
            $item = $this->itemBuilder->buildOffer($offer);
            if ($item) $item_set->push($item);
        }
        $section = (new Section())->setType(Sections::BANNER_GROUP)->setHeight($offerGroup->getHeight())->setRatio($offerGroup->getRatio())->setUpdatedAt($offerGroup->updated_at ?: $this->mockUpdatedAt);
        return (!$item_set->isEmpty()) ? $this->setItems($section, $item_set) : null;
    }

    public function buildGrid(Grid $grid, $location_id)
    {
        $item_set = new ItemSet();
        foreach ($grid->blocks as $block) {
            $type = $block->item_type ? strtolower(class_basename($block->item_type)) : null;
            if ($type && $type == 'category') {
                $locations = $block->item->locations ? $block->item->locations->filter(function ($item) use ($location_id) {
                    return $item->id == $location_id;
                }) : null;
                $item = (!$locations->isEmpty()) ? $this->itemBuilder->buildCategory($block->item) : null;
                if ($item) {
                    $children_items = [];
                    $children = $block->item->children()->whereHas('locations', function ($query) use ($location_id) {
                        $query->where('id', $location_id);
                    })->published()->get();
                    foreach ($children as $child) {
                        $children_items[] = $this->itemBuilder->buildCategory($child)->toArray();
                    }
                    $item->setChildren($children_items);
                }
            } elseif ($type && $type == 'categorygroup') {
                $locations = $block->item->locations ? $block->item->locations->filter(function ($item) use ($location_id) {
                    return $item->id == $location_id;
                }) : null;
                $item = (!$locations->isEmpty()) ? $this->itemBuilder->buildCategoryGroup($block->item) : null;
                if ($item) {
                    $children_items = [];
                    $children = $block->item->categories()->whereHas('locations', function ($query) use ($location_id) {
                        $query->where('id', $location_id);
                    })->published()->get();
                    foreach ($children as $child) {
                        $children_items[] = $this->itemBuilder->buildCategory($child)->toArray();
                    }
                    $item->setChildren($children_items);
                }
            } elseif ($type && $type == 'voucher') {
                $item = $this->itemBuilder->buildVoucher($block->item);
            } else {
                $item = null;
            }
            if ($item) $item_set->push($item);
        }
        $section = (new Section())->setType(Sections::GRID)->setUpdatedAt($this->mockUpdatedAt);
        return $this->setItems($section, $item_set);
    }

    private function setItems(Section $section, ItemSet $item_set)
    {
        foreach ($item_set as $item) {
            /** @var  Item $item */
            $section->pushItem($item);
        }
        return $section;
    }
}
