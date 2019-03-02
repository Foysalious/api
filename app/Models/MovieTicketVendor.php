<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovieTicketVendor extends Model
{
    public function commissions()
    {
        return $this->hasMany( MovieTicketVendorCommission::class, 'movie_ticket_vendor_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', 1);
    }
}
