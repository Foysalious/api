<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $guarded = ['id'];

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeSort($query)
    {
        return $query->orderBy('order');
    }

    public function scopeShow($query)
    {
        return $query->active()->sort()->get();
    }
}
