<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'mobile',
        'email',
        'password',
        'remember_token',
        'driver_id',
        'fb_id',
        'google_id',
        'mobile_verified',
        'email_verified',
        'address',
        'permanent_address',
        'bkash_agreement_id',
        'occupation',
        'nid_no',
        'nid_image_front',
        'nid_image_back',
        'tin_no',
        'tin_certificate',
        'gender',
        'dob',
        'pro_pic',
        'total_asset_amount',
        'monthly_living_cost',
        'monthly_loan_installment_amount',
        'utility_bill_attachment',
        'nominee_id',
        'nominee_relation',
        'grantor_id',
        'grantor_relation',
        'created_by',
        'created_by_name',
        'updated_by',
        'updated_by_name',
        'created_at',
        'updated_at',
    ];

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function resource()
    {
        return $this->hasOne(Resource::class);
    }

    public function affiliate()
    {
        return $this->hasOne(Affiliate::class);
    }

    public function member()
    {
        return $this->hasOne(Member::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function joinRequests()
    {
        return $this->hasMany(JoinRequest::class);
    }

    public function posCustomer()
    {
        return $this->hasOne(PosCustomer::class);
    }

    public function getIdentityAttribute()
    {
        if ($this->name != '') {
            return $this->name;
        } elseif ($this->mobile) {
            return $this->mobile;
        }
        return $this->email;
    }
}
