<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Laravel\Scout\Searchable;
use Sheba\Business\Procurement\Statuses;
use Sheba\Business\Procurement\Type;
use Sheba\Dal\Procurement\PublicationStatuses;
use Sheba\Dal\ProcurementInvitation\Model as ProcurementInvitation;
use Sheba\Dal\ProcurementPaymentRequest\Model as ProcurementPaymentRequest;
use Illuminate\Database\Eloquent\Model;
use Sheba\Payment\PayableType;
use Sheba\Business\Procurement\Code\Builder as CodeBuilder;
use Sheba\Dal\Category\Category;

class Procurement extends Model implements PayableType
{
    use Searchable;

    protected $guarded = ['id'];
    protected $dates = ['closed_and_paid_at', 'procurement_start_date', 'procurement_end_date', 'last_date_of_submission', 'published_at'];
    public $paid;
    public $due;
    public $totalPrice;
    /** @var CodeBuilder $codeBuilder */
    private $codeBuilder;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->codeBuilder = new CodeBuilder();
    }

    public function items()
    {
        return $this->hasMany(ProcurementItem::class);
    }

    public function questions()
    {
        return $this->hasMany(ProcurementQuestion::class);
    }

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    public function paymentRequests()
    {
        return $this->hasMany(ProcurementPaymentRequest::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function getTagNamesAttribute()
    {
        return $this->tags->pluck('name');
    }

    public function owner()
    {
        return $this->morphTo();
    }

    public function invitations()
    {
        return $this->hasMany(ProcurementInvitation::class);
    }

    public function scopeOrder($query)
    {
        return $query->whereIn('status', ['accepted', 'started', 'served', 'cancelled']);
    }

    public function calculate()
    {
        if ($this->paid) return;
        $bid = $this->getActiveBid();
        $this->paid = $this->sheba_collection + $this->partner_collection;
        $this->due = $bid ? $bid->price - $this->paid : 0;
        $this->totalPrice = $bid ? $bid->price : null;
    }

    public function getActiveBid()
    {
        return $this->bids->where('status', config('b2b.BID_STATUSES')['accepted'])->first();
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function hasAccepted()
    {
        return $this->status == config('b2b.PROCUREMENT_STATUS')['accepted'];
    }

    public function isServed()
    {
        return $this->status == config('b2b.PROCUREMENT_STATUS')['served'];
    }

    public function isClosedAndPaid()
    {
        return $this->closed_and_paid_at != null;
    }

    public function isAdvanced()
    {
        return $this->type == Type::ADVANCED;
    }

    public function orderCode()
    {
        return $this->codeBuilder->order($this);
    }

    public function workOrderCode()
    {
        return $this->codeBuilder->workOrder($this);
    }

    public function invoiceCode()
    {
        return $this->codeBuilder->invoice($this);
    }

    public function billCode()
    {
        return $this->codeBuilder->bill($this);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getRemainingDays()
    {
        $today = Carbon::now();
        if (!$this->last_date_of_submission->greaterThanOrEqualTo($today)) return 0;
        return $this->last_date_of_submission->diffInDays($today) + 1;
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'short_description' => $this->short_description,
            'long_description' => $this->long_description,
            'last_date_of_submission_timestamp' => $this->last_date_of_submission->timestamp,
            '_tags' => $this->getTagNamesAttribute()->toArray()
        ];
    }

    /**
     * Scope a query to only include jobs of a given status.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOpenForPublic($query)
    {
        return $query
            ->whereIn('status', Statuses::getOpen())
            ->where('last_date_of_submission', '>=', Carbon::now());
    }

    public function scopePublished($query)
    {
        return $query->where('publication_status', PublicationStatuses::PUBLISHED);
    }
}
