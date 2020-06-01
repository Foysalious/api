<?php namespace Sheba\Partner\HomePageSettingV3\Calculators;

use Exception;
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
            if (is_null($this->partner->home_page_setting))
                throw new Exception();
            return json_decode($this->partner->home_page_setting);
        } catch (Exception $e) {
            $data = $this->next->get();
            (new PartnerRepository($this->partner))->update($this->partner, ['home_page_setting' => json_encode($data)]);
            return $data;
        }
    }
}