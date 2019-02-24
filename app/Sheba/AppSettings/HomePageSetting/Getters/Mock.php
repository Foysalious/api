<?php namespace Sheba\AppSettings\HomePageSetting\Getters;

use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\OfferShowcase;
use App\Models\Voucher;
use Sheba\AppSettings\HomePageSetting\DS\ItemSet;
use Sheba\AppSettings\HomePageSetting\DS\Setting;

class Mock extends Getter
{
    /**
     * @return Setting
     */
    public function getSettings() : Setting
    {
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

    private function menu()
    {
        $items = (new ItemSet())->push($this->itemBuilder->buildCategoryGroup(CategoryGroup::find(1)))
            ->push($this->itemBuilder->buildTopUp())
            ->push($this->itemBuilder->buildFavourites())
            ->push($this->itemBuilder->buildOfferList())
            ->push($this->itemBuilder->buildSubscriptionList());
        return $this->sectionBuilder->buildMenu($items);
    }

    private function categories()
    {
        $items = new ItemSet();
        $category = $this->itemBuilder->buildCategory(Category::find(1));
        for ($i=1; $i<=30; $i++) {
            $items->push($category);
        }
        return $this->sectionBuilder->buildCategories($items);
    }

    private function subscriptionBanner()
    {
        $items = (new ItemSet())->push($this->itemBuilder->buildSubscriptionList());
        return $this->sectionBuilder->buildBanner($items, 300);
    }

    private function offerList()
    {
        return $this->sectionBuilder->buildOfferList();
    }

    private function mediumBanner()
    {
        $items = (new ItemSet())->push($this->itemBuilder->buildOffer(OfferShowcase::find(1)));
        return $this->sectionBuilder->buildBanner($items, 300);
    }

    private function subscriptionList()
    {
        return $this->sectionBuilder->buildSubscriptionList();
    }

    private function bigBanner()
    {
        $items = (new ItemSet())->push($this->itemBuilder->buildOffer(OfferShowcase::find(1)));
        return $this->sectionBuilder->buildBanner($items, 400);
    }

    private function categoryGroup()
    {
        $category_group = CategoryGroup::find(1);
        $items = new ItemSet();
        foreach ($category_group->categories as $category) {
            $items->push($this->itemBuilder->buildCategory($category));
        }
        return $this->sectionBuilder->buildCategoryGroup($category_group, $items);
    }

    private function smallBannerArray()
    {
        $items = (new ItemSet())->push($this->itemBuilder->buildCategory(Category::find(1)))
            ->push($this->itemBuilder->buildCategory(Category::find(10)))
            ->push($this->itemBuilder->buildVoucher(Voucher::where('code', 'KHELAHOBE')->first()));
        return $this->sectionBuilder->buildBanner($items, 200);
    }
}