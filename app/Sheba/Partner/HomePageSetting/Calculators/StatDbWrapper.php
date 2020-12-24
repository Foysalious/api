<?php namespace Sheba\Partner\HomePageSetting\Calculators;

use Exception;
use Sheba\Partner\HomePageSetting\Setting;
use Sheba\Repositories\PartnerRepository;

class StatDbWrapper extends Setting
{
    public function __construct(Setting $next = null)
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