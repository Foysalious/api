<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopUpVendor extends Model
{
    use HasFactory;
    
    protected $guarded = ['id'];
    protected $table = 'topup_vendors';
    protected $casts = [
        'sheba_commission' => 'double',
        'agent_commission' => 'double',
        'is_published'     => 'int',
        'amount'           => 'double',
    ];

    public function commissions()
    {
        return $this->hasMany(TopUpVendorCommission::class, 'topup_vendor_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', 1);
    }

    public function scopeGateway($query, $gateway)
    {
        return $query->where('gateway', $gateway);
    }
}
