<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceGroup extends Model
{
    //
    protected $guarded = [];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_group_service');
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'service_group_location');
    }

    public function togglePublishedForApp()
    {
        $this->is_published_for_app = !$this->is_published_for_app;
        return $this;
    }

    public function togglePublishedForWeb()
    {
        $this->is_published_for_web = !$this->is_published_for_web;
        return $this;
    }

    public function scopePublishedFor($query, $type = 'app')
    {
        if ($type == 'app') {
            return $this->where('is_published_for_app', 1);
        } else if ($type == 'web') {
            return $this->where('is_published_for_web', 1);
        } else {
            return $this->where('is_published_for_app', 1)->where('is_published_for_web', 1);
        }
    }
}
