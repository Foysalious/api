<?php namespace Sheba\Partner\HomePageSetting\Calculators;

use Sheba\Partner\HomePageSetting\CacheManager;
use Sheba\Partner\HomePageSetting\Setting;

class CacheWrapper extends Setting
{
    public function __construct(Setting $next = null)
    {
        parent::__construct($next);
    }

    protected function setting()
    {
        /** @var CacheManager $cache_manager */
        $cache_manager = new CacheManager();
        $cache_manager->setVersion($this->version)->setPartner($this->partner);
        if ($cache_manager->has()) {
            return $cache_manager->get();
        } else {
            $data =  $this->next->setVersion($this->version)->get();;
            $cache_manager->store($data);
            return $data;
        }
    }
}