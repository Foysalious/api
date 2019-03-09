<?php namespace Sheba\Settings\Payment\Factory;

use Sheba\Settings\Payment\Methods\BkashSetting;

class PaymentSettingProcessor
{
    private $method;

    /**
     * @param $method
     * @return $this
     */
    public function setMethodName($method)
    {
        $this->method = $method;
        return $this;
    }


    /**
     * @return BkashSetting
     * @throws \ReflectionException
     */
    public function get()
    {
        $this->method = $this->calculate();
        return $this->method;
    }


    /**
     * @return bool
     * @throws \ReflectionException
     */
    private function isValidMethod()
    {
        return in_array($this->method, (new \ReflectionClass(PaymentSettingStrategy::class))->getStaticProperties());
    }


    /**
     * @return BkashSetting
     * @throws \ReflectionException
     */
    private function calculate()
    {
        if (!$this->isValidMethod()) throw new \InvalidArgumentException('Invalid Method.');

        switch ($this->method) {
            case 'bkash':
                return new BkashSetting();
        }
    }
}