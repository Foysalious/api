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

    public function issue()
    {
        return $this->hasOne(InspectionItemIssue::class);
    }

    public function isRadio()
    {
        return $this->input_type == 'radio';
    }

    public function scopeFailedItems($query, $business)
    {
        return $query->whereHas('inspection', function ($q) use ($business) {
            $q->with('vehicle.basicInformation')->where('business_id', $business->id);
        })->where('input_type', 'radio')->where('result', 'LIKE', '%failed%')
            ->orderBy('id', 'desc');
    }

    public function scopeOpenIssues($query)
    {
        return $query->whereHas('issue', function ($q) {
            $q->where('status', 'open');
        })->where('status', 'issue_created')->count();
    }

    public function scopePendingItems($query)
    {
        return $query->where('status', 'open')->count();
    }
}