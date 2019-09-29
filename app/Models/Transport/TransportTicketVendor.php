<?php namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Model;

class TransportTicketVendor extends Model
{
    protected $guarded = ['id'];

    public function commissions()
    {
        return $this->hasMany(TransportTicketVendorCommission::class, 'vendor_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', 1);
    }
}
