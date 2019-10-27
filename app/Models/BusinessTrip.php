<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Comment\MorphCommentable;
use Sheba\Comment\MorphComments;

class BusinessTrip extends Model implements MorphCommentable
{
    use MorphComments;

    protected $guarded = ['id'];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function getTripReadableTypeAttribute()
    {
        return title_case(str_replace('_', ' ', $this->trip_type));
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function scopeOngoing($query)
    {
        return $query->where('status', 'open')->orWhere('status', 'process');
    }

    /**
     * @inheritDoc
     */
    public function getNotificationHandlerClass()
    {
        // TODO: Implement getNotificationHandlerClass() method.
    }
}
