<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerPosCustomer extends Model
{
    protected $guarded = ['id'];

    public function customer()
    {
        return $this->belongsTo(PosCustomer::class);
    }

    public function scopeByPartner($query, $partner_id)
    {
        return $query->where('partner_id', $partner_id);
    }

    public function scopeByPartnerAndCustomer($query, $partner_id, $customer_id)
    {
        return $query->where('partner_id', $partner_id)->where('customer_id',$customer_id);
    }

    public function details()
    {
        $customer = $this->customer;
        $profile = $customer->profile;
        return [
            'id' => $customer->id,
            'name' => $profile->name,
            'phone' => $profile->mobile,
            'email' => $profile->email,
            'address' => $profile->address,
            'image' => $profile->pro_pic,
            'note' => $this->note
        ];
    }
    public function scopeDueDateReminder($query,$partner_id,$customer_id){
        return $query->where('partner_id', $partner_id)->where('customer_id',$customer_id)->pluck('due_date_reminder');

    }
}
