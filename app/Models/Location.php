<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model {
    protected $fillable = [
        'name',
        'city_id',
        'publication_status'
    ];

    public function partners()
    {
        return $this->belongsToMany(Partner::class);
    }

}
