<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    protected $guarded = ['id',];
    protected $dates = ['created_at','updated_at'];
    protected $table = 'inspections';

    public function scopePublished($query)
    {
        return $query->where('is_published', 1);
    }

    public function formTemplate()
    {
        return $this->belongsTo(FormTemplate::class, 'form_template_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function items()
    {
        return $this->hasMany(InspectionItem::class);
    }
}