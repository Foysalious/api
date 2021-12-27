<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Sheba\Dal\PartnerPosCategory\PartnerPosCategory;

class PosCategory extends Model
{
    use HasFactory;
    
    protected $guarded = ['id'];

    public function parent()
    {
        return $this->belongsTo(PosCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(PosCategory::class, 'parent_id');
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

    public function deletedServices()
    {
        return $this->services()->onlyTrashed();
    }

    public function services()
    {
        return $this->hasMany(PartnerPosService::class);
    }

    public function partnerPosCategory()
    {
        return $this->hasMany(PartnerPosCategory::class, 'category_id');
    }

    public function scopeMasterCategoryByPartner($q, $partner_id)
    {
        return $q->leftJoin(
            'partner_pos_categories',
            'pos_categories.id',
            '=',
            'partner_pos_categories.category_id'
        )->where('partner_pos_categories.partner_id', $partner_id)->whereNull('pos_categories.parent_id');
    }
}
