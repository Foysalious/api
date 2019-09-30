<?php namespace Sheba\Bkash\Modules\Normal;

use Sheba\Bkash\Modules\BkashAuth;
use Sheba\Bkash\Modules\BkashAuthBuilder;
use Sheba\Bkash\Modules\BkashModule;
use Sheba\Bkash\Modules\Normal\Methods\Other\SupportingOperation;
use Sheba\Bkash\Modules\Normal\Methods\Payout\NormalPayout;

class NormalModule extends BkashModule
{
    public function __construct()
    {
        $this->setBkashAuth();
        $this->setToken();
    }

    protected function setToken()
    {
        $this->token = new NormalToken();
    }

    public function setBkashAuth()
    {
        $this->bkashAuth = BkashAuthBuilder::set018BkashAuth();
    }

    public function getToken()
    {
        return $this->token->setBkashAuth($this->bkashAuth)->get();
    }

    public function getMethod($enum)
    {
        if ($enum == 'payout') return new NormalPayout();
        if ($enum == 'support') return new SupportingOperation(); else return null;
    }
}