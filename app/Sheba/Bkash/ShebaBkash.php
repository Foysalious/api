<?php namespace Sheba\Bkash;

use Sheba\Bkash\Modules\BkashModule;
use Sheba\Bkash\Modules\Normal\NormalModule;
use Sheba\Bkash\Modules\Tokenized\TokenizedModule;

class ShebaBkash
{
    /**
     * @var $module BkashModule
     */
    private $module;

    public function setModule($enum)
    {
        if ($enum == 'tokenized') $this->module = new TokenizedModule();
        elseif ($enum == 'normal') $this->module = new NormalModule();
        return $this;
    }

    public function getToken()
    {
        return $this->module->getToken();
    }

    public function getModuleMethod($method_name)
    {
        return $this->module->getMethod($method_name);
    }
}