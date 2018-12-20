<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    protected $guarded = ['id'];

    public function item()
    {
        return $this->morphTo();
    }
}
