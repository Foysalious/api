<?php namespace Sheba\AppSettings\HomePageSetting\Supported;

use Sheba\AppSettings\HomePageSetting\Exceptions\UnsupportedSection;

class Sections extends Constants
{
    const MENU = "menu";
    const MASTER_CATEGORIES = "master_categories";
    const GRID = "grid";
    const SLIDER = "slider";
    const BANNER = "banner";
    const BANNER_GROUP = "banner_group";
    const CATEGORY_GROUP = "category_group";
    const OFFER_LIST = "offer_list";
    const SUBSCRIPTION_LIST = "subscription_list";
    const TOPUP = 'top_up';

    /**
     * @param $section
     * @throws UnsupportedSection
     */
    protected static function throwException($section)
    {
        throw new UnsupportedSection($section);
    }
}
