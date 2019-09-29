<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScreenSettingElement extends Model
{
    protected $guarded = ['id'];

    public function item()
    {
        return $this->morphTo();
    }
}