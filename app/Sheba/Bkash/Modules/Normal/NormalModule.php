<?php namespace Sheba\Bkash\Modules\Normal;


use Sheba\Bkash\Modules\BkashAuth;
use Sheba\Bkash\Modules\BkashModule;
use Sheba\Bkash\Modules\Normal\Methods\Payout\NormalPayout;

class NormalModule extends BkashModule
{
    /** @var $token NormalToken */
    private $token;
    /** @var $bkashAuth BkashAuth */
    protected $bkashAuth;

    public function __construct()
    {
        $this->setBkashAuth();
        $this->token = new NormalToken();
    }

    public function setBkashAuth()
    {
        $this->bkashAuth = new BkashAuth();
        $this->bkashAuth->setKey(config('bkash.app_key'))
            ->setSecret(config('bkash.app_secret'))
            ->setUsername(config('bkash.username'))
            ->setPassword(config('bkash.password'))->setUrl(config('bkash.url'));
    }

    public function getToken()
    {
        return $this->token->setBkashAuth($this->bkashAuth)->get();
    }

    public function getMethod($enum)
    {
        if ($enum == 'payout') return new NormalPayout();
    }


}