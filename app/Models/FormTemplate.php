<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormTemplate extends Model
{
    protected $guarded = ['id',];
    protected $table = 'form_templates';

    public function items()
    {
        return $this->hasMany(FormTemplateItem::class);
    }

    public function questions()
    {
        return $this->hasMany(FormTemplateQuestion::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', 1);
    }

    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }

    public function scopeFor($query, $for)
    {
        return $query->where('type', $for);
    }

    public function scopeBusinessOwner($query, $business_id)
    {
        return $query->where('owner_type', "App\\Models\\Business")->where('owner_id', $business_id);
    }
}