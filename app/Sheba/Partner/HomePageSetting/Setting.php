<?php namespace Sheba\Partner\HomePageSetting;

use App\Models\Partner;
use Illuminate\Support\Collection;

abstract class Setting
{
    /** @var Setting */
    protected $next;
    /** @var Partner $partner */
    protected $partner;

    protected $version;

    public function __construct(Setting $next = null)
    {
        $this->next = $next;
    }

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        $this->next->partner = $this->partner;

        return $this;
    }

    public function get()
    {
        return $this->setting();
    }

    /** @return Collection */
    protected abstract function setting();
}