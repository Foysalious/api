<?php namespace Sheba\Reward\Event;

use Illuminate\Database\Eloquent\Builder;

abstract class EventParameter
{
    public $value;

    abstract public function validate();

    abstract public function check(Builder $query);
}