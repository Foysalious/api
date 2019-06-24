<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    protected $guarded = ['id'];
    protected $dates = ['start_date', 'next_start_date'];
    protected $table = 'inspections';

    public function scopePublished($query)
    {
        return $query->where('is_published', 1);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['open', 'process']);
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

    public function inspectionSchedule()
    {
        return $this->belongsTo(InspectionSchedule::class);
    }

    public function isClosed()
    {
        return $this->status == 'closed';
    }

    public function getNextStartDate()
    {
        if ($this->inspectionSchedule) {
            $inspection = $this->inspectionSchedule->inspections()->published()->active()->where('start_date', '>', $this->start_date)->first();
            return $inspection ? $inspection->start_date : null;
        } else {
            return null;
        }
    }
}