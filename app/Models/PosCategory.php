<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosCategory extends Model
{
    protected $guarded = ['id'];

    public function parent()
    {
        return $this->belongsTo(PosCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(PosCategory::class, 'parent_id');
    }

    public function services()
    {
        return $this->hasMany(PartnerPosService::class);
    }

    public function scopeParents($query)
    {
        $query->where('parent_id', null);
    }

    public function scopeChild($query)
    {
        $query->where('parent_id', '<>', null);
    }

    public function scopePublished($query)
    {
        $query->where('publication_status', 1);
    }
}
