<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionItem extends Model
{
    protected $guarded = ['id',];
    protected $table = 'inspection_items';

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function inspectionItemIssues()
    {
        return $this->hasMany(InspectionItemIssue::class);
    }

    public function isRadio()
    {
        return $this->input_type == 'radio';
    }
}