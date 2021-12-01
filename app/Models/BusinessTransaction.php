<?php namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Sheba\Helpers\TimeFrame;
use Sheba\Transactions\Types as TransactionTypes;

class BusinessTransaction extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['amount' => 'double'];
    public $timestamps = false;
    protected $dates = ['created_at'];

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeTag($query, $tag)
    {
        return $query->where('tag', $tag);
    }

    public function isDebit()
    {
        return $this->type == TransactionTypes::DEBIT;
    }

    public function isCredit()
    {
        return $this->type == TransactionTypes::CREDIT;
    }

    public function balance($balance_before)
    {
        return $balance_before + (($this->isDebit() ? -1 : 1) * $this->amount);
    }

    public function scopeCreatedAtBetweenTimeFrame($query, TimeFrame $time_frame)
    {
        return $query->whereBetween('created_at', $time_frame->getArray());
    }
}
