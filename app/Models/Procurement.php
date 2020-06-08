<?php namespace App\Models;

use AlgoliaSearch\Laravel\AlgoliaEloquentTrait;
use Carbon\Carbon;
use Sheba\Business\Procurement\Type;
use Sheba\Dal\ProcurementPaymentRequest\Model as ProcurementPaymentRequest;
use Illuminate\Database\Eloquent\Model;
use Sheba\Payment\PayableType;
use Sheba\Business\Procurement\Code\Builder as CodeBuilder;

class Procurement extends Model implements PayableType
{
    use AlgoliaEloquentTrait;

    protected $guarded = ['id'];
    protected $dates = ['closed_and_paid_at', 'procurement_start_date', 'procurement_end_date', 'last_date_of_submission'];
    public $paid;
    public $due;
    public $totalPrice;
    /** @var CodeBuilder $codeBuilder */
    private $codeBuilder;
    public $algoliaSettings = ['searchableAttributes' => ['title', 'name', '_tags', 'short_description', 'long_description']];

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

    public function getAlgoliaRecord()
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
}
