<?php namespace Sheba\Reward\Event;

use Illuminate\Database\Eloquent\Builder;

abstract class CampaignEventParameter extends EventParameter
{
    abstract public function check(Builder $query);
}