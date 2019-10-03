<?php namespace Sheba\Reward\Event\Customer\Action\OrderServedAndPaid;

use Sheba\Reward\Event\ActionRule;
use Sheba\Reward\Event\Customer\Action\OrderServedAndPaid\Parameter\Amount;
use Sheba\Reward\Event\Customer\Action\OrderServedAndPaid\Parameter\PaymentMethod;
use Sheba\Reward\Event\Customer\Action\OrderServedAndPaid\Parameter\SalesChannel;

class Rule extends ActionRule
{
    /** @var Amount */
    public $amount;
    /** @var PaymentMethod */
    public $paymentMethod;
    /** @var SalesChannel */
    public $salesChannel;

    /**
     * @throws \Sheba\Reward\Exception\ParameterTypeMismatchException
     */
    public function validate()
    {
        $this->amount->validate();
        $this->paymentMethod->validate();
        $this->salesChannel->validate();
    }

    public function makeParamClasses()
    {
        $this->amount        = new Amount();
        $this->paymentMethod = new PaymentMethod();
        $this->salesChannel  = new SalesChannel();
    }

    public function setValues()
    {
        $this->amount->value        = property_exists($this->rule, 'amount') ? $this->rule->amount : null;
        $this->paymentMethod->value = property_exists($this->rule, 'payment_methods') ? $this->rule->payment_methods : null;
        $this->salesChannel->value  = property_exists($this->rule, 'sales_channels') ? $this->rule->sales_channels : null;
    }

    public function check(array $params)
    {
        return $this->amount->check($params) && $this->paymentMethod->check($params) && $this->salesChannel->check($params);
    }
}