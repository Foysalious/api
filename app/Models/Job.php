<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model {

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function partner_order()
    {
        return $this->belongsTo(PartnerOrder::class);
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function paymentLogs()
    {
        return $this->hasMany(JobPaymentLog::class);
    }

    public function materials()
    {
        return $this->belongsToMany(Material::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}
