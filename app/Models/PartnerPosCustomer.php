<?php namespace App\Models;

use App\Sheba\Customer\Events\PartnerPosCustomerCreatedEvent;
use App\Sheba\Customer\Events\PartnerPosCustomerUpdatedEvent;
use Sheba\Dal\BaseModel;

class PartnerPosCustomer extends BaseModel
{
    protected $guarded = ['id'];

    public static $createdEventClass = PartnerPosCustomerCreatedEvent::class;
    public static $updatedEventClass = PartnerPosCustomerUpdatedEvent::class;

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
        return $query->where('partner_id', $partner_id)->where('customer_id', $customer_id);
    }

    public function details()
    {
        $customer = $this->customer;
        $profile = $customer->profile;
        return [
            'id' => $customer->id,
            'name' => $this->nick_name ?: ($profile ? $profile->name : null),
            'phone' => $profile ? $profile->mobile : null,
            'email' => $profile ? $profile->email : null,
            'address' => $profile ? $profile->address : null,
            'image' => $profile ? $profile->pro_pic : null,
            'note' => $this->note,
            'is_supplier' => $this->is_supplier

        ];
    }

    public function scopeDueDateReminder($query, $partner_id, $customer_id)
    {
        return $query->where('partner_id', $partner_id)->where('customer_id', $customer_id)->pluck('due_date_reminder');

    }

    public function scopeGetPartnerPosCustomerName($query, $partner_id, $customer_id)
    {
        $partnerPosCustomer = $query->where('partner_id', $partner_id)->where('customer_id', $customer_id)->first();
        if ($partnerPosCustomer && $partnerPosCustomer->nick_name) return $partnerPosCustomer->nick_name;
        $customer = PosCustomer::find((int)$customer_id);
        return $customer->profile->name;
    }
}
