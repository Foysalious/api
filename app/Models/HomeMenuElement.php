<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeMenuElement extends Model
{

    protected $guarded = ['id'];

    public function menu()
    {
        $this->belongsTo(HomeMenu::class);
    }
}
