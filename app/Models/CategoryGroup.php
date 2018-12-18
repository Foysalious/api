<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryGroup extends Model
{
    protected $guarded = ['id'];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_group_category');
    }

    public function scopePublishedForApp($q)
    {
        return $q->where('is_published_for_app', 1);
    }

    public function scopePublishedForWeb($q)
    {
        return $q->where('is_published_for_web', 1);
    }
}
