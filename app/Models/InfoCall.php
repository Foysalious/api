<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfoCall extends Model
{
    protected $guarded = ['id'];

    protected $dates = ['follow_up_date', 'intended_closing_date'];

    public function handlingDepartment()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function crm()
    {
        return $this->belongsTo(User::class, 'crm_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function code()
    {
        return "I-" . sprintf("%06d", $this->id);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function complains()
    {
        return $this->morphMany(Complain::class, 'complainable');
    }

    public function order()
    {
        return $this->hasOne(Order::class);
    }

    /**
     * Scope a query to only include infocalls of a given status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatus($query, $status)
    {
        $query->where('status', $status);
    }

    /**
     * Scope a query to only include infocalls of a given status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $priority
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePriority($query, $priority)
    {
        $query->where('priority', $priority);
    }
}
