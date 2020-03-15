<?php namespace App\Sheba\Release;


use Illuminate\Support\Facades\Redis;

class Release
{

    public function set($release)
    {
        if ($release) Redis::set('releases::api', $release);
    }

    public function get()
    {
        return Redis::get('releases::api');
    }
}