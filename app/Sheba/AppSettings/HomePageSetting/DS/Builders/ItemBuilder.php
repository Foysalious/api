<?php namespace Sheba\AppSettings\HomePageSetting\DS\Builders;

use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\ExternalProject;
use App\Models\OfferShowcase;
use App\Models\Service;
use App\Models\ServiceSubscription;
use App\Models\Slide;
use App\Models\Voucher;
use Sheba\AppSettings\HomePageSetting\DS\Item;
use Sheba\AppSettings\HomePageSetting\Supported\Targets;

class ItemBuilder
{
    public function buildCategoryGroup(CategoryGroup $category_group)
    {
        return (new Item())->setTargetType(Targets::CATEGORY_GROUP)
            ->setTargetId($category_group->id)
            ->setName($category_group->name)
            ->setIcon($category_group->icon)
            ->setIconPng($category_group->icon_png)
            ->setUpdatedAt($category_group->updated_at);
    }

    public function buildTopUp()
    {
        return (new Item())->setTargetType(Targets::TOP_UP)->setName('Top Up');
    }

    public function buildNearByPartners()
    {
        return (new Item())->setTargetType(Targets::NEAR_BY_PARTNERS)->setName('Near By');
    }

    public function buildFavourites()
    {
        return (new Item())->setTargetType(Targets::FAVOURITES)->setName('Favourites');
    }

    public function buildOfferList()
    {
        return (new Item())->setTargetType(Targets::OFFER_LIST)->setName('Offers');
    }

    public function buildSubscriptionList()
    {
        return (new Item())->setTargetType(Targets::SUBSCRIPTION_LIST)->setName('Subscription');
    }

    public function buildCategory(Category $category)
    {
        $target = $category->isParent() ? Targets::MASTER_CATEGORY : Targets::SECONDARY_CATEGORY;
        return (new Item())->setTargetId($category->id)->setTargetType($target)->setIsParent($category->isParent())->setName($category->name)->setIcon($category->icon_png)->setIconPng($category->icon)->setAppThumb($category->app_thumb)->setUpdatedAt($category->updated_at)->setThumb($category->thumb)->setBanner($category->banner)->setAppBanner($category->app_banner)->setSlug($category->slug);
    }

    public function buildOffer(OfferShowcase $offer)
    {
        $item = $offer->target_type !== 'none' ? $this->buildFromTarget($offer->target_type, $offer->target) : $this->buildNone($offer);
        if (!$item) {
            $item = (new Item())->setTargetType(Targets::OFFER)->setTargetId($offer->id);
        }
        return $item->setIsFlash($offer->is_flash)->setHeight($offer->getHeight())->setRatio($offer->getRatio())->setAppBanner($offer->app_banner)->setBanner($offer->banner)->setAppThumb($offer->app_thumb)->setUpdatedAt($offer->updated_at);
    }

    public function buildSlide(Slide $slide)
    {
        $item = $this->buildFromTarget($slide->target_type, $slide->target);
        if (!$item) {
            $item = (new Item())->setTargetId($slide->id);
        }
        return $item->setAppThumb($slide->small_image_link)->setAppBanner($slide->image_link)->setBanner($slide->image_link)
            ->setIcon($slide->small_image_link)->setIconPng($slide->image_link);
    }

    private function buildNone(OfferShowcase $offerShowcase)
    {
        return (new Item())->setTargetType(Targets::OFFER)->setLink($offerShowcase->target_link);
    }

    private function buildFromTarget($type, $target)
    {
        $type = $type ? strtolower(class_basename($type)) : null;
        if ($type == 'category') {
            $item = $this->buildCategory($target);
        } elseif ($type == 'voucher') {
            $item = $this->buildVoucher($target);
        } elseif ($type == 'offershowcase') {
            $item = $this->buildOffer($target);
        } else if ($type == 'categorygroup') {
            $item = $this->buildCategoryGroup($target);
        } else if ($type == 'service') {
            $item = $this->buildService($target);
        } else if ($type === 'externalproject') {
            $item = $this->buildExternalProject($target);
        } else if ($type === 'profile') {
            $item = $this->buildReferral();
        } else if($type === 'infocall') {
            $item = $this->buildInfoCall();
        } else {
            return null;
        }
        return $item;
    }

    public function buildReferral()
    {
        return (new Item())->setTargetType(Targets::REFERRALS);
    }

    public function buildInfoCall()
    {
        return (new Item())->setTargetType(Targets::INFO_CALL);
    }

    public function buildExternalProject(ExternalProject $project)
    {
        return (new Item())->setTargetType(Targets::EXTERNAL_PROJECT)->setPackageName($project->app_link)->setLink($project->web_link);
    }

    public function buildService(Service $service)
    {
        return (new Item())->setTargetType(Targets::SERVICE)->setTargetId($service->id)->setAppBanner($service->app_banner)->setAppThumb($service->app_thumb)->setUpdatedAt($service->updated_at);
    }

    public function buildVoucher(Voucher $voucher)
    {
        return (new Item())->setTargetType(Targets::VOUCHER)->setTargetId($voucher->id)->setVoucherCode($voucher->code)->setUpdatedAt($voucher->updated_at);
    }

    public function buildMenu($element, $location_id)
    {
        if ($element->item_type == 'subscription_list') {
            $subscriptions = ServiceSubscription::whereHas('service', function ($q) use ($location_id) {
                $q->whereHas('locations', function ($qa) use ($location_id) {
                    $qa->where('id', $location_id);
                });
            })->get();
            if ($subscriptions->isEmpty()) return null;
        }
        if ($element->item_type == 'category_group') {
            $categoryGroup = CategoryGroup::where('id', $element->item_id)->whereHas('locations', function ($q) use ($location_id) {
                $q->where('id', $location_id);
            })->get();
            if ($categoryGroup->isEmpty()) return null;
        }
        $item = (new Item())->setName($element->name)->setTargetType($element->item_type)->setTargetId((int)$element->item_id)->setIcon($element->icon)->setUpdatedAt($element->updated_at);
        if ($element->item_type === 'category_group') {
            $categoryGroup=CategoryGroup::find($element->item_id);
            if($categoryGroup){
                $categories = $categoryGroup->categories()->whereHas('locations', function ($query) use ($location_id) {
                    $query->where('id', $location_id);
                })->get();
                $children = [];
                foreach ($categories as $category) {
                    $children[] = $this->buildCategory($category)->toArray();
                }
                $item->setChildren($children);
            }
        }
        return $item;
    }
}
