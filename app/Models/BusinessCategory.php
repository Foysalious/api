<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessCategory extends Model
{
    protected $guarded = ['id'];

    public function businesses()
    {
        return $this->hasMany(Business::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
}
