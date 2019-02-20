<?php namespace Sheba\AppSettings\HomePageSetting\Getters;

use Carbon\Carbon;
use Sheba\AppSettings\HomePageSetting\Sections;
use Sheba\AppSettings\HomePageSetting\Settings;
use Sheba\AppSettings\HomePageSetting\Targets;

class Mock extends Getter
{
    /**
     * @return Settings
     */
    public function getSettings() : Settings
    {
        $setting = new Settings();
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
        return [
            'type' => Sections::MENU,
            'items' => [
                [
                    'type' => Targets::CATEGORY_GROUP,
                    'id' => 1,
                    'name' => 'Beast Deal',
                    'icon' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/icons_png/1543400128_tiwnn.png',
                ], [
                    'type' => Targets::TOP_UP,
                    'name' => 'Top Up',
                    'icon' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/icons_png/1543400128_tiwnn.png',
                ], [
                    'type' => Targets::FAVOURITES,
                    'name' => 'Favourites',
                    'icon' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/icons_png/1543400128_tiwnn.png',
                ], [
                    'type' => Targets::OFFER_LIST,
                    'name' => 'Offers',
                    'icon' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/icons_png/1543400128_tiwnn.png',
                ], [
                    'type' => Targets::SUBSCRIPTION_LIST,
                    'name' => 'Subscription',
                    'icon' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/icons_png/1543400128_tiwnn.png',
                ]
            ],
            'updated_at' => Carbon::parse('2019-01-01')
        ];
    }

    private function categories()
    {
        $items = [];
        for ($i=1; $i<=30; $i++) {
            $items[] = [
                'id' => 1,
                'name' => 'Appliance Repair',
                'icon' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/icons_png/1543400128_tiwnn.png',
                'app_thumb' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Sub-catagory/10/150.jpg'
            ];
        }
        return [
            'type' => Sections::MASTER_CATEGORIES,
            'items' => $items,
            'updated_at' => Carbon::parse('2019-01-01')
        ];
    }

    private function subscriptionBanner()
    {
        return [
            'type' => Sections::BANNER,
            'items' => [
                [
                    'type' => Targets::SUBSCRIPTION_LIST,
                    'banner' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/1495262683_home_appliances_.png',
                    'height' => 200,
                ]
            ],
            'updated_at' => Carbon::parse('2019-01-01')
        ];
    }

    private function offerList()
    {
        return [
            'type' => Sections::OFFER_LIST,
            'items' => [],
            'updated_at' => Carbon::parse('2019-01-01')
        ];
    }

    private function mediumBanner()
    {
        return [
            'type' => Sections::BANNER,
            'items' => [
                [
                    'type' => Targets::SUBSCRIPTION_LIST,
                    'banner' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/1495262683_home_appliances_.png',
                    'height' => 300,
                ]
            ],
            'is_flash' => true,
            'updated_at' => Carbon::parse('2019-01-01')
        ];
    }

    private function subscriptionList()
    {
        return [
            'type' => Sections::SUBSCRIPTION_LIST,
            'items' => [],
            'updated_at' => Carbon::parse('2019-01-01')
        ];
    }

    private function bigBanner()
    {
        return [
            'type' => Sections::BANNER,
            'items' => [
                [
                    'type' => null,
                    'banner' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/1495262683_home_appliances_.png',
                    'height' => 400,
                ]
            ],
            'updated_at' => Carbon::parse('2019-01-01')
        ];
    }

    private function categoryGroup()
    {
        $items = [];
        for ($i=1; $i<=30; $i++) {
            $items[] = [
                'id' => 10,
                'name' => 'Ac',
                'icon' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/icons_png/1543400128_tiwnn.png',
                'app_thumb' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Sub-catagory/10/150.jpg'
            ];
        }
        return [
            'type' => Sections::CATEGORY_GROUP,
            'id' => 1,
            'name' => 'Trending Services',
            'categories' => $items
        ];
    }

    private function smallBannerArray()
    {
        return [
            'type' => Sections::BANNER,
            'items' => [
                [
                    'type' => Targets::MASTER_CATEGORY,
                    'id' => 1,
                    'banner' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/1495262683_home_appliances_.png',
                    'height' => 200,
                ], [
                    'type' => Targets::SECONDARY_CATEGORY,
                    'id' => 10,
                    'banner' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/1495262683_home_appliances_.png',
                    'height' => 200,
                ], [
                    'type' => Targets::VOUCHER,
                    'id' => 1,
                    'code' => 'KHELAHOBE',
                    'banner' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/1495262683_home_appliances_.png',
                    'height' => 200,
                ]
            ]
        ];
    }
}