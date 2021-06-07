<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Dal\Category\Category;
use Sheba\Dal\Extras\Traits\HasLocationScope;
use App\Models\ScreenSettingElement;

class CategoryGroup extends Model
{
    use HasLocationScope;

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

    public function locations()
    {
        return $this->belongsToMany(Location::class);
    }

    public function screenSettingElements()
    {
        return $this->morphMany(ScreenSettingElement::class, 'item');
    }
}
