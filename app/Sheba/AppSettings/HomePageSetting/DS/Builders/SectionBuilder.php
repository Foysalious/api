<?php namespace Sheba\AppSettings\HomePageSetting\DS\Builders;

use App\Models\CategoryGroup;
use Carbon\Carbon;
use Sheba\AppSettings\HomePageSetting\DS\Item;
use Sheba\AppSettings\HomePageSetting\DS\ItemSet;
use Sheba\AppSettings\HomePageSetting\DS\Section;
use Sheba\AppSettings\HomePageSetting\Supported\Sections;

class SectionBuilder
{
    /* TODO REMOVE */
    private $mockUpdatedAt;
    
    public function __construct()
    {
        $this->mockUpdatedAt = Carbon::parse('2019-01-01');
    }

    public function buildMenu(ItemSet $items)
    {
        $section = (new Section())->setType(Sections::MENU)->setName("Menu")->setUpdatedAt($this->mockUpdatedAt);
        return $this->setItems($section, $items);
    }

    public function buildCategories(ItemSet $categories)
    {
        $section = (new Section())->setType(Sections::MASTER_CATEGORIES)->setName("Our Categories")->setUpdatedAt($this->mockUpdatedAt);
        return $this->setItems($section, $categories);
    }

    public function buildBanner(ItemSet $items, $height)
    {
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

    public function buildCategoryGroup(CategoryGroup $category_group, ItemSet $categories)
    {
        $section = (new Section())->setName($category_group->name)->setId($category_group->id)
            ->setType(Sections::CATEGORY_GROUP)->setUpdatedAt($this->mockUpdatedAt);
        return $this->setItems($section, $categories);
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