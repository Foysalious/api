<?php namespace App\Sheba\Release;


use Illuminate\Support\Facades\Redis;

class Release
{

    public function set($release)
    {
        if ($release) Redis::set('releases::' . $this->getSentryProjectNameOfThisProject(), $release);
    }

    public function get()
    {
        return Redis::get('releases::' . $this->getSentryProjectNameOfThisProject());
    }

    private function getSentryProjectNameOfThisProject()
    {
        return config('sentry.project_name');
    }
}