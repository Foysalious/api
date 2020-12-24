<?php namespace Sheba\Order\Policy;


abstract class Orderable
{
    abstract public function canOrder();
}