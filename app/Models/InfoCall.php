<?php namespace Sheba\Dal\InfoCall;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Sheba\Comment\MorphCommentable;
use Sheba\Comment\MorphComments;
use Sheba\Dal\BaseModel;
use Sheba\Dal\Category\Category;
use Sheba\Dal\InfoCallStatusLogs\InfoCallStatusLog;
use Sheba\Dal\Service\Service;

class InfoCall extends BaseModel implements MorphCommentable
{
    use MorphComments;

    protected $guarded = ['id'];

    protected $dates = ['follow_up_date', 'intended_closing_date'];

    public function handlingDepartment()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
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
    
    public function statusLogs()
    {
        return $this->hasMany(InfoCallStatusLog::class);
    }

    /**
     * Scope a query to only include infocalls of a given status.
     *
     * @param Builder $query
     * @param $status
     * @return void
     */
    public function scopeStatus($query, $status)
    {
        $query->where('status', $status);
    }

    /**
     * Scope a query to only include infocalls of a given status.
     *
     * @param Builder $query
     * @param $priority
     * @return void
     */
    public function scopePriority($query, $priority)
    {
        $query->where('priority', $priority);
    }

    /**
     * @inheritDoc
     */
    public function getNotificationHandlerClass()
    {
        // TODO: Implement getNotificationHandlerClass() method.
    }
}
