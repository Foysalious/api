<?php namespace App\Models;

use Sheba\Dal\BaseModel;

class CategoryScheduleSlot extends BaseModel
{
    protected $table = 'category_schedule_slot';

    public function scopeCategory($query, $category_id)
    {
        return $query->where('category_id', $category_id);
    }

    public function scopeDay($query, $day)
    {
        return $query->where('day', $day);
    }

    public function slot()
    {
        return $this->belongsTo(ScheduleSlot::class,  'schedule_slot_id');
    }
}