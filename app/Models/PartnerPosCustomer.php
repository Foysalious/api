<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerPosCustomer extends Model
{
    protected $guarded = ['id'];

    public function customer()
    {
        return $this->belongsTo(PosCustomer::class);
    }

    public function details()
    {
        $customer = $this->customer;
        $profile = $customer->profile;
        return [
          'id' => $this->id,
          'name' => $profile->name,
          'phone' => $profile->mobile,
          'email' => $profile->email,
          'address' => $profile->address,
          'image' => $profile->pro_pic,
          'note' => $this->note,
        ];
    }
}
