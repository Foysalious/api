<?php namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Sheba\Dal\Service\Service;

class NavService extends Eloquent
{
    protected $connection = 'mongodb';

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    public function scopePublished($query)
    {
        return $query->where('publication_status', 1);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}