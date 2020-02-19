<?php namespace App\Models;

use Sheba\Dal\TripRequestApproval\Model as TripRequestApproval;
use App\Http\Controllers\B2b\TripRequestApprovalController;
use Illuminate\Database\Eloquent\Model;
use Sheba\Comment\MorphCommentable;
use Sheba\Comment\MorphComments;

class BusinessTripRequest extends Model implements MorphCommentable
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

    public function tripRequestApprovals()
    {
        return $this->hasMany(TripRequestApproval::class);
    }

    public function getBusinessMember()
    {
        return BusinessMember::where([['member_id', $this->member_id], ['business_id', $this->business_id]])->first();
    }

    public function getTripReadableTypeAttribute()
    {
        return title_case(str_replace('_', ' ', $this->trip_type));
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * @inheritDoc
     */
    public function getNotificationHandlerClass()
    {
        // TODO: Implement getNotificationHandlerClass() method.
    }
}
