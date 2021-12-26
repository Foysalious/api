<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Sheba\Voucher\Contracts\CanApplyVoucher;

class PosCustomer extends Model implements CanApplyVoucher
{
    use HasFactory;

    protected $guarded = ['id'];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function details()
    {
        $profile = $this->profile;

        return [
            'id'      => $this->id,
            'name'    => $profile->name,
            'phone'   => $profile->mobile,
            'email'   => $profile->email,
            'address' => $profile->address,
            'image'   => $profile->pro_pic,
        ];
    }

    public function isEditable()
    {
        $permissible_user_type = ['Resource', 'Partner'];

        return $this->profile->created_by && in_array(
                explode('-', $this->profile->created_by_name)[0],
                $permissible_user_type
            );
    }

    public function scopeGetByMobile($query, $mobile)
    {
        return $query->select('pos_customers.*')->leftJoin(
            'profiles',
            'profiles.id',
            '=',
            'pos_customers.profile_id'
        )->where('profiles.mobile', 'LIKE', "%$mobile%");
    }

    public function partnerPosCustomer()
    {
        return $this->hasMany("App\\Models\\PartnerPosCustomer", 'customer_id');
    }

    public function movieTicketOrders()
    {
        return $this->morphMany(MovieTicketOrder::class, 'agent');
    }
}
