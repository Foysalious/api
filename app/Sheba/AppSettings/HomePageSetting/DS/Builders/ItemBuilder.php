<?php namespace Sheba\AppSettings\HomePageSetting\DS\Builders;

use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\OfferShowcase;
use App\Models\Voucher;
use Carbon\Carbon;
use Sheba\AppSettings\HomePageSetting\DS\Item;
use Sheba\AppSettings\HomePageSetting\Supported\Targets;

class ItemBuilder
{
    /* TODO REMOVE */
    private $mockUpdatedAt;
    private $mockIcon = 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/icons_png/1543400128_tiwnn.png';
    private $mockThumb = 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Sub-catagory/10/150.jpg';
    private $mockBanner = 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/1495262683_home_appliances_.png';

    public function __construct()
    {
        $this->mockUpdatedAt = Carbon::parse('2019-01-01');
    }

    public function buildCategoryGroup(CategoryGroup $category_group)
    {
        return (new Item())
            ->setTargetType(Targets::CATEGORY_GROUP)
            ->setTargetId($category_group->id)
            ->setName($category_group->name)
            ->setIcon($category_group->icon)
            ->setUpdatedAt($category_group->updated_at);
    }
    
    public function buildTopUp()
    {
        return (new Item())->setTargetType(Targets::TOP_UP)->setName('Top Up')
            ->setIcon($this->mockIcon)->setUpdatedAt($this->mockUpdatedAt);
    }
    
    public function buildFavourites()
    {
        return (new Item())->setTargetType(Targets::FAVOURITES)->setName('Favourites')
            ->setIcon($this->mockIcon)->setUpdatedAt($this->mockUpdatedAt);
    }
    
    public function buildOfferList()
    {
        return (new Item())->setTargetType(Targets::OFFER_LIST)->setName('Offers')
            ->setIcon($this->mockIcon)->setAppBanner($this->mockBanner)
            ->setUpdatedAt($this->mockUpdatedAt);
    }
    
    public function buildSubscriptionList()
    {
        return (new Item())->setTargetType(Targets::SUBSCRIPTION_LIST)->setName('Subscription')
            ->setIcon($this->mockIcon)->setAppBanner($this->mockBanner)
            ->setUpdatedAt($this->mockUpdatedAt);
    }

    public function buildCategory(Category $category)
    {
        $target = $category->isParent() ? Targets::MASTER_CATEGORY : Targets::SECONDARY_CATEGORY;
        return (new Item())->setTargetId($category->id)->setTargetType($target)->setIsParent($category->isParent())
            ->setName($category->name)->setIcon($category->icon_png)->setAppThumb($category->app_thumb)
            ->setUpdatedAt($this->mockUpdatedAt);
    }

    public function buildOffer(OfferShowcase $offer)
    {
        return (new Item())->setTargetType(Targets::OFFER)->setTargetId($offer->id)
            ->setAppBanner($offer->banner)->setUpdatedAt($this->mockUpdatedAt);
    }

    public function buildVoucher(Voucher $voucher)
    {
        return (new Item())->setTargetType(Targets::VOUCHER)->setTargetId($voucher->id)
            ->setAppBanner($this->mockBanner)->setVoucherCode($voucher->code)
            ->setUpdatedAt($this->mockUpdatedAt);
    }
}