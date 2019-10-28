<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Comment\MorphCommentable;
use Sheba\Comment\MorphComments;

class CustomOrder extends Model implements MorphCommentable
{
    use MorphComments;

    protected $guarded = ['id'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function discussions()
    {
        return $this->hasMany(CustomOrderDiscussion::class);
    }

    public function cancelLog()
    {
        return $this->hasOne(CustomOrderCancelLog::class);
    }

    public function statusLog()
    {
        return $this->hasmany(CustomOrderStatusLog::class);
    }

    public function updateLog()
    {
        return $this->hasMany(CustomOrderUpdateLog::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @inheritDoc
     */
    public function getNotificationHandlerClass()
    {
        // TODO: Implement getNotificationHandlerClass() method.
    }
}
