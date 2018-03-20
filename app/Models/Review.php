<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeIsEmptyReview($query)
    {
        return $query->where('review', '<>', '');
    }

    public function scopeNotEmptyReview($query)
    {
        return $query->where('review', '<>', '');
    }

    public function rate()
    {
        return $this->hasOne(ReviewQuestionAnswer::class, 'review_id');
    }
}