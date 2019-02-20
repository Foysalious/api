<?php namespace Sheba\AppSettings\HomePageSetting\Getters;

use Sheba\AppSettings\HomePageSetting\Settings;

class Mock extends Getter
{
    /**
     * @return Settings
     */
    public function getSettings() : Settings
    {
        $setting = new Settings();
        $setting->push($this->getMenu());
        $setting->push($this->getMenu());
        $setting->push($this->getMenu());
        $setting->push($this->getMenu());
        $setting->push($this->getMenu());
        $setting->push($this->getMenu());
        return $setting;
    }

    private function getMenu()
    {
        return [
            'type' => 'menu',
            'items' => [
                [
                    'type' => 'category_group',
                    'id' => 1,
                    'name' => 'Beast Deal',
                    'icon' => '',
                ], [
                    'type' => 'top_up',
                    'name' => 'Top Up',
                    'icon' => '',
                ], [
                    'type' => 'favourites',
                    'name' => 'Favourites',
                    'icon' => '',
                ], [
                    'type' => 'offer_list',
                    'name' => 'Offers',
                    'icon' => '',
                ], [
                    'type' => 'subscription_list',
                    'name' => 'Subscription',
                    'icon' => '',
                ]
            ]
        ];
    }
}