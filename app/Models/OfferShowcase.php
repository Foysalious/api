<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferShowcase extends Model
{
    protected $guarded = ['id'];

    public function scopeActive($q)
    {
        return $q->where('is_active', 1);
    }
}
