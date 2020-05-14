<?php namespace Sheba\Partner\HomePageSettingV3\Calculators;

use Sheba\Partner\HomePageSettingV3\CacheManager;
use Sheba\Partner\HomePageSettingV3\SettingV3;

class CacheWrapper extends SettingV3
{
    public function __construct(SettingV3 $next = null)
    {
        parent::__construct($next);
    }

    protected function setting()
    {
        /** @var CacheManager $cache_manager */
        $cache_manager = new CacheManager();
        $cache_manager->setPartner($this->partner);
        if ($cache_manager->has()) {
            return $cache_manager->get();
        } else {
            $data = $this->next->get();
            $cache_manager->store($data);
            return $data;
        }
    }
}