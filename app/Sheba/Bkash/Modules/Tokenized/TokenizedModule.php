<?php

namespace Sheba\Bkash\Modules\Tokenized;


use Sheba\Bkash\Modules\BkashAuth;
use Sheba\Bkash\Modules\BkashModule;
use Sheba\Bkash\Modules\Tokenized\Methods\Agreement\TokenizedAgreement;

class TokenizedModule extends BkashModule
{
    public function __construct()
    {
        $this->setBkashAuth();
        $this->setToken();
    }

    public function setBkashAuth()
    {
        $this->bkashAuth = new BkashAuth();
        $this->bkashAuth->setKey(config('bkash.tokenized.app_key'))
            ->setSecret(config('bkash.tokenized.app_secret'))
            ->setUsername(config('bkash.tokenized.username'))
            ->setPassword(config('bkash.tokenized.password'))->setUrl(config('bkash.tokenized.url'));
    }

    public function getToken()
    {
        return $this->token->setBkashAuth($this->bkashAuth)->get();
    }

    /**
     * @param $enum
     * @return TokenizedAgreement|TokenizedPayment
     */
    public function getMethod($enum)
    {
        if ($enum == 'agreement') return new TokenizedAgreement();
        elseif ($enum == 'payment') return (new TokenizedPayment())->setBkashAuth($this->bkashAuth);
    }


    protected function setToken()
    {
        $this->token = new TokenizedToken();
    }
}