<?php namespace Sheba\AppSettings\HomePageSetting\DS\Builders;

use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\Grid;
use App\Models\HomeMenu;
use App\Models\OfferGroup;
use App\Models\OfferShowcase;
use App\Models\Slider;
use Carbon\Carbon;
use Sheba\AppSettings\HomePageSetting\DS\Item;
use Sheba\AppSettings\HomePageSetting\DS\ItemSet;
use Sheba\AppSettings\HomePageSetting\DS\Section;
use Sheba\AppSettings\HomePageSetting\Supported\Sections;

class SectionBuilder
{
    /* TODO REMOVE */
    private $mockUpdatedAt, $mockHeight;
    private $itemBuilder;

    public function __construct(ItemBuilder $builder)
    {
        $this->mockUpdatedAt = Carbon::parse('2019-01-01');
        $this->itemBuilder = $builder;
        $this->mockHeight = 300;
    }

    public function buildMenu(HomeMenu $menu)
    {
        $section = (new Section())->setType(Sections::MENU)->setName("Menu")->setUpdatedAt($this->mockUpdatedAt);
        $items = new ItemSet();
        foreach ($menu->elements as $element) {
            $items->push($this->itemBuilder->buildMenu($element));
        }
        return $this->setItems($section, $items);
    }

    public function buildCategories()
    {
        $masterCategories = Category::query()->parents()->published()->get();
        $categories = new ItemSet();
        foreach ($masterCategories as $category) {
            $item = $this->itemBuilder->buildCategory($category);
            $categories->push($item);
        }
        $section = (new Section())->setType(Sections::MASTER_CATEGORIES)->setName("Our Categories")->setUpdatedAt($this->mockUpdatedAt);
        return $this->setItems($section, $categories);
    }

    public function buildBanner(OfferShowcase $offer)
    {
        $item = $this->itemBuilder->buildOffer($offer);
        $items = (new ItemSet())->push($item);
        $height = isset($offer->height) ? $offer->height : $this->mockHeight;
        $section = (new Section())->setType(Sections::BANNER)->setHeight($height)->setUpdatedAt($this->mockUpdatedAt);
        return $this->setItems($section, $items);
    }

    public function buildSubscriptionList()
    {
        return (new Section())->setType(Sections::SUBSCRIPTION_LIST)->setUpdatedAt($this->mockUpdatedAt);
    }

    public function buildOfferList()
    {
        return (new Section())->setType(Sections::OFFER_LIST)->setUpdatedAt($this->mockUpdatedAt);

    }

    public function buildSlider(Slider $slider)
    {
        $item_set = new ItemSet();
        foreach ($slider->slides as $slide) {
            $type = $slide->target_type ? strtolower(class_basename($slide->target_type)) : null;
            if ($type && $type == 'category') {
                $item = $this->itemBuilder->buildCategory($slide->target);
            } elseif ($type && $type == 'voucher') {
                $item = $this->itemBuilder->buildVoucher($slide->target);
            } else {
                $item = $this->itemBuilder->buildSlide($slide);
            }
            $item_set->push($item);
        }
        $section = (new Section())->setType(Sections::SLIDER)->setUpdatedAt($this->mockUpdatedAt);
        return $this->setItems($section, $item_set);
    }

    public function buildCategoryGroup(CategoryGroup $category_group)
    {
        $categories = new ItemSet();
        $section = (new Section())->setName($category_group->name)->setId($category_group->id)
            ->setType(Sections::CATEGORY_GROUP)->setUpdatedAt($this->mockUpdatedAt);
        foreach ($category_group->categories as $category) {
            $categories->push($this->itemBuilder->buildCategory($category));
        }
        return $this->setItems($section, $categories);
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
            $item_set->push($item);
        }
        $section = (new Section())->setType(Sections::BANNER_GROUP)->setHeight($this->mockHeight)->setUpdatedAt($this->mockUpdatedAt);
        return $this->setItems($section, $item_set);
    }

    public function buildGrid(Grid $grid)
    {
        $item_set = new ItemSet();
        foreach ($grid->blocks as $block) {
            $type = $block->item_type ? strtolower(class_basename($block->item_type)) : null;
            if ($type && $type == 'category') {
                $item = $this->itemBuilder->buildCategory($block->item);
            } elseif ($type && $type == 'voucher') {
                $item = $this->itemBuilder->buildCategoryGroup($block->item);
            } else {
                $item = null;
            }
            if ($item) $item_set->push($item);
        }
        $section=(new Section())->setType(Sections::GRID)->setUpdatedAt($this->mockUpdatedAt);
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
