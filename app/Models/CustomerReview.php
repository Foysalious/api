<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerReview extends Model
{
    protected $guarded = ['id'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function rates()
    {
        return $this->hasMany(ReviewQuestionAnswer::class, 'review_id');
    }
}
