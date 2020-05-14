<?php namespace Sheba\Partner\HomePageSettingV3;

use App\Models\Partner;
use Illuminate\Support\Collection;

abstract class SettingV3
{
    /** @var SettingV3 */
    protected $next;
    /** @var Partner $partner */
    protected $partner;

    protected $version;

    public function __construct(SettingV3 $next = null)
    {
        $this->next = $next;
    }

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        $this->next->partner = $this->partner;

        return $this;
    }

    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    public function get()
    {
        return $this->setting();
    }

    /** @return Collection */
    protected abstract function setting();
}