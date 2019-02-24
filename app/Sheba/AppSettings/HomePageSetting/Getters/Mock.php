<?php namespace Sheba\AppSettings\HomePageSetting\Getters;

use Carbon\Carbon;
use Sheba\AppSettings\HomePageSetting\DS\Item;
use Sheba\AppSettings\HomePageSetting\DS\Section;
use Sheba\AppSettings\HomePageSetting\DS\Setting;
use Sheba\AppSettings\HomePageSetting\Supported\Sections;
use Sheba\AppSettings\HomePageSetting\Supported\Targets;

class Mock extends Getter
{
    private $icon = 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/icons_png/1543400128_tiwnn.png';
    private $thumb = 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Sub-catagory/10/150.jpg';
    private $banner = 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/1495262683_home_appliances_.png';
    private $updatedAt;

    /**
     * @return Setting
     */
    public function getSettings() : Setting
    {
        $this->updatedAt = Carbon::parse('2019-01-01');
        $setting = new Setting();
        $setting->push($this->menu());
        $setting->push($this->categories());
        $setting->push($this->subscriptionBanner());
        $setting->push($this->offerList());
        $setting->push($this->mediumBanner());
        $setting->push($this->subscriptionList());
        $setting->push($this->bigBanner());
        $setting->push($this->categoryGroup());
        $setting->push($this->smallBannerArray());
        return $setting;
    }

    /**
     * @return Section
     */
    private function menu()
    {
        $section = new Section();
        $section->setType(Sections::MENU)->setName("Menu")->setUpdatedAt($this->updatedAt);
        $category_group = (new Item())->setTargetType(Targets::CATEGORY_GROUP)->setTargetId(1)->setName('Beast Deal')->setIcon($this->icon)->setUpdatedAt($this->updatedAt);
        $top_up = (new Item())->setTargetType(Targets::TOP_UP)->setName('Top Up')->setIcon($this->icon)->setUpdatedAt($this->updatedAt);
        $favourites = (new Item())->setTargetType(Targets::FAVOURITES)->setName('Favourites')->setIcon($this->icon)->setUpdatedAt($this->updatedAt);
        $offer_list = (new Item())->setTargetType(Targets::OFFER_LIST)->setName('Offers')->setIcon($this->icon)->setUpdatedAt($this->updatedAt);
        $subscription_list = (new Item())->setTargetType(Targets::SUBSCRIPTION_LIST)->setName('Subscription')->setIcon($this->icon)->setUpdatedAt($this->updatedAt);
        $section->pushItem($category_group)->pushItem($top_up)->pushItem($favourites)->pushItem($offer_list)->pushItem($subscription_list);
        return $section;
    }

    private function categories()
    {
        $section = (new Section())->setType(Sections::MASTER_CATEGORIES)->setName("Our Categories")->setUpdatedAt($this->updatedAt);
        for ($i=1; $i<=30; $i++) {
            $section->pushItem((new Item())->setTargetId(1)->setTargetType(Targets::MASTER_CATEGORY)->setName('Appliance Repair')->setIcon($this->icon)->setAppThumb($this->thumb)->setUpdatedAt($this->updatedAt));
        }
        return $section;
    }

    private function subscriptionBanner()
    {
        $section = (new Section())->setType(Sections::BANNER)->setUpdatedAt($this->updatedAt);
        $section->pushItem((new Item())->setTargetType(Targets::SUBSCRIPTION_LIST)->setAppBanner($this->banner)->setHeight(200)->setUpdatedAt($this->updatedAt));
        return $section;
    }

    private function offerList()
    {
        $section = (new Section())->setType(Sections::OFFER_LIST)->setUpdatedAt($this->updatedAt);
        return $section;
    }

    private function mediumBanner()
    {
        $section = (new Section())->setType(Sections::BANNER)->setUpdatedAt($this->updatedAt);
        $section->pushItem((new Item())->setTargetType(Targets::OFFER_LIST)->setAppBanner($this->banner)->setHeight(300)->setUpdatedAt($this->updatedAt));
        return $section;
    }

    private function subscriptionList()
    {
        $section = (new Section())->setType(Sections::SUBSCRIPTION_LIST)->setUpdatedAt($this->updatedAt);
        return $section;
    }

    private function bigBanner()
    {
        $section = (new Section())->setType(Sections::BANNER)->setUpdatedAt($this->updatedAt);
        $section->pushItem((new Item())->setTargetType(Targets::OFFER)->setAppBanner($this->banner)->setHeight(400)->setUpdatedAt($this->updatedAt));
        return $section;
    }

    private function categoryGroup()
    {
        $section = (new Section())->setType(Sections::BANNER)->setUpdatedAt($this->updatedAt);
        for ($i=1; $i<=10; $i++) {
            $section->pushItem((new Item())->setTargetType(Targets::SECONDARY_CATEGORY)->setAppThumb($this->thumb)->setName('Ac')->setUpdatedAt($this->updatedAt));
        }
        return $section;
    }

    private function smallBannerArray()
    {
        $section = (new Section())->setType(Sections::BANNER)->setUpdatedAt($this->updatedAt);
        $mc = (new Item())->setTargetType(Targets::MASTER_CATEGORY)->setTargetId(1)->setAppBanner($this->banner)->setHeight(200)->setUpdatedAt($this->updatedAt);
        $sc = (new Item())->setTargetType(Targets::SECONDARY_CATEGORY)->setTargetId(10)->setAppBanner($this->banner)->setHeight(200)->setUpdatedAt($this->updatedAt);
        $voucher = (new Item())->setTargetType(Targets::VOUCHER)->setAppBanner($this->banner)->setVoucherCode('KHELAHOBE')->setHeight(200)->setUpdatedAt($this->updatedAt);
        $section->pushItem($mc)->pushItem($sc)->pushItem($voucher);
        return $section;
    }
}