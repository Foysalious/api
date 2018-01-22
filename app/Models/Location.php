<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'name',
        'city_id',
        'publication_status'
    ];

    public function partners()
    {
        return $this->belongsToMany(Partner::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function custom_orders()
    {
        return $this->hasMany(CustomOrder::class);
    }

    public function scopePublished($query)
    {
        return $query->where('publication_status', 1);
    }

}
