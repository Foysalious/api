<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosCustomer extends Model
{
    protected $guarded = ['id'];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function details()
    {
        $profile = $this->profile;
        return [
            'id' => $this->id,
            'name' => $profile->name,
            'phone' => $profile->mobile,
            'email' => $profile->email,
            'address' => $profile->address,
            'image' => $profile->pro_pic
        ];
    }

    public function isEditable()
    {
        $permissible_user_type = ['Resource', 'Partner'];
        return $this->profile->created_by &&
            in_array(explode('-', $this->profile->created_by_name)[0], $permissible_user_type);
    }
}
