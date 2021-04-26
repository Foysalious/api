<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Transactions\Types;

class AffiliateTransaction extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['amount' => 'double'];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function affiliation()
    {
        return $this->morphTo();
    }

    public function scopeEarning($query)
    {
        return $query->where([
            ['type', '=', Types::CREDIT],
            ['log', 'NOT LIKE', '%Moneybag Refilled%'],
            ['log', 'NOT LIKE', '%Manually Received%'],
            ['log', 'NOT LIKE', '%Credit Purchase%'],
            ['log', 'NOT LIKE', '%is refunded%'],
            ['log', 'NOT LIKE', '%manually refunded in your account%'],
            ['log', 'NOT LIKE', '%Sheba facilitated amount%']
        ]);
    }

    public function scopeCredit($query)
    {
        return $query->where('type', '=', Types::CREDIT);
    }

    public function scopeDebit($query)
    {
        return $query->where('type','=',Types::DEBIT);
    }

    public function scopeBalanceRecharge($query)
    {
        return $query->where('log', 'LIKE', "%Credit Purchase%");
    }

    public function scopeBetween($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    public function scopeServiceCommission($query)
    {
        return $query->where('log', 'LIKE', "%for reference%");
    }

    public function scopeTransportTicket($query)
    {
        return $query->where('log', 'LIKE', "%Transport Ticket%");
    }

    public function scopeServicePurchase($query)
    {
        return $query->where('log', 'LIKE', "%Service Purchase%");
    }

    public function scopeMovieTicket($query)
    {
        return $query->where('log', 'LIKE', '%Movie Ticket%');
    }

    public function scopeMovieTicketCommission($query)
    {
        return $query->where('log', 'LIKE', '%movie ticket sales commission%');
    }

    public function scopeRefunds($query)
    {
        return $query->where('log', 'LIKE', '%is refunded%')
            ->orWhere('log', 'LIKE', '%manually refunded in your account%')
            ->orWhere('log', 'LIKE', "%received as refund%")
            ->orWhere('log', 'LIKE', "%Refund for Product Resell%");
    }

    public function scopeManualDisbursement($query)
    {
        return $query->where('log', 'LIKE', "%received from manual disbursement%")
                     ->orWhere('log', 'LIKE', "%received as TopUp Commission%");
    }

    public function scopeShebaFacilitated($query)
    {
        return $query->where('log', 'LIKE', "%Sheba facilitated amount%");
    }


    public function scopeBusTicketCommission($query)
    {
        return $query->where('log', 'LIKE', '%bus ticket sales commission%');
    }
}
