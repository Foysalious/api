<?php namespace Sheba\Partner\HomePageSettingV3\Calculators;

use Exception;
use Sheba\Partner\HomePageSettingV3\NewFeatures;
use Sheba\Partner\HomePageSettingV3\SettingV3;
use Sheba\Repositories\PartnerRepository;

class StatDbWrapper extends SettingV3
{
    public function __construct(SettingV3 $next = null)
    {
        parent::__construct($next);
    }
    protected function setting()
    {
        try {
            if (is_null($this->partner->home_page_setting_new))
                throw new Exception();
            $home_page_setting = json_decode($this->partner->home_page_setting_new, 1);
            foreach ($home_page_setting as &$setting) {
                in_array($setting['key'], NewFeatures::get()) ? $setting['is_new'] = 1 : $setting['is_new'] = 0;
            }
            return $home_page_setting;
        } catch (Exception $e) {
            $data = $this->next->get();
            (new PartnerRepository($this->partner))->update($this->partner, ['home_page_setting_new' => json_encode($data)]);
            return $data;
        }
    }
}