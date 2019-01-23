<?php namespace Sheba\Reward\Event;

abstract class ActionRule extends Rule
{
    public function check(array $params)
    {
        foreach ($this->params as $key => $param) {
            $param = $param['object'];
            /** @var $param ActionEventParameter */
            if(!$param->check($params)) return false;
        }

        return true;
    }
}