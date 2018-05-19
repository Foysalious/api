<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
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

    public function rates()
    {
        return $this->hasMany(ReviewQuestionAnswer::class, 'review_id');
    }

    public function getCalculatedReviewAttribute()
    {
        if (!empty($this->review)) {
            return $this->review;
        } elseif (count($this->rates) > 0) {
            foreach ($this->rates as $rate) {
                if (!empty($rate->rate_answer_text)) {
                    return $rate->rate_answer_text;
                }
            }
        }
        return "";
    }
}