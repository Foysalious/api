<?php namespace Sheba\Events;


abstract class ShebaEvent
{
    public function getUserId()
    {
        return request()->hasHeader('User-Id') && request()->header('User-Id') != null ? (int)request()->header('User-Id') : null;
    }
}