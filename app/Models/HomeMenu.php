<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeMenu extends Model
{
    //
    protected $guarded = ['id'];

    public function elements()
    {
        return $this->hasMany(HomeMenuElement::class);
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'home_menu_location');
    }
}
