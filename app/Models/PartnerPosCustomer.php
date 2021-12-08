<?php namespace App\Models;

use App\Sheba\Customer\Events\AccountingCustomerCreate;
use Illuminate\Database\Eloquent\Model;

class PartnerPosCustomer extends Model
{
    protected $guarded = ['id'];

    public static $savedEventClass =AccountingCustomerCreate::class;
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
            'name' => $this->nick_name ? $this->nick_name : $profile->name,
            'phone' => $profile->mobile,
            'email' => $profile->email,
            'address' => $profile->address,
            'image' => $profile->pro_pic,
            'note' => $this->note,
            'is_supplier' => $this->is_supplier

        ];
    }
    public function scopeDueDateReminder($query,$partner_id,$customer_id){
        return $query->where('partner_id', $partner_id)->where('customer_id',$customer_id)->pluck('due_date_reminder');

    }

    public function scopeGetPartnerPosCustomerName($query,$partner_id,$customer_id) {
        $partnerPosCustomer = $query->where('partner_id', $partner_id)->where('customer_id',$customer_id)->first();
        if($partnerPosCustomer && $partnerPosCustomer->nick_name) return $partnerPosCustomer->nick_name;
        $customer = PosCustomer::find((int)$customer_id);
        return $customer->profile->name;
    }
}
