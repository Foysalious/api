<?php namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class InspectionSchedule extends Model
{
    protected $guarded = ['id'];

    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }
}