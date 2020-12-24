<?php namespace Sheba\Reward\Event;

abstract class Rule
{
    protected $rule;

    /** @var array */
    protected $params;

    public function __construct($rule, array $params)
    {
        $this->rule = is_string($rule) ? json_decode($rule) : $rule;
        $this->params = $params;

        $this->makeParamClasses();
        //$this->setValues();
        //$this->validate();
    }

    public function makeParamClasses()
    {
        foreach ($this->params as $key => $param) {
            /** @var $param EventParameter */
            $param = new $param['class'];
            $param->value = property_exists($this->rule, $key) ? $this->rule->$key : null;
            $param->validate();
            $this->params[$key]['object'] = $param;
        }
    }

    /*public function setValues()
    {
        foreach ($this->params as $key => $param) {
            $param = $param['object'];
            $param->value = property_exists($this->rule, $key) ? $this->rule->$key : null;
        }
    }

    public function validate()
    {
        foreach ($this->params as $param) {
            $param = $param['object'];
            $param->validate();
        }
    }*/
}