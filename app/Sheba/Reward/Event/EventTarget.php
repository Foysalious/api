<?php namespace Sheba\Reward\Event;

use Illuminate\Database\Eloquent\Builder;

interface EventTarget
{
    public function calculateProgress(Builder $query);

    public function getAchieved();

    public function getTarget();

    public function hasAchieved();
}