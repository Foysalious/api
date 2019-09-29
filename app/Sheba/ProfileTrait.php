<?php namespace Sheba;

trait ProfileTrait
{
    public function getNameAttribute()
    {
        return ($this->profile) ? $this->profile->name : null;
    }

    public function getMobileAttribute()
    {
        return ($this->profile) ? $this->profile->mobile : null;
    }

    public function getContactNoAttribute()
    {
        return ($this->profile) ? $this->profile->mobile : null;
    }

    public function getEmailAttribute()
    {
        return ($this->profile) ? $this->profile->email : null;
    }

    public function getFbIdAttribute()
    {
        return ($this->profile) ? $this->profile->fb_id : null;
    }

    public function getEmailVerifiedAttribute()
    {
        return ($this->profile) ? boolval($this->profile->email_verified) : null;
    }

    public function getMobileVerifiedAttribute()
    {
        return ($this->profile) ? boolval($this->profile->mobile_verified) : null;
    }

    public function getPasswordAttribute()
    {
        return ($this->profile) ? $this->profile->password : null;
    }

    public function getProPicAttribute()
    {
        return ($this->profile) ? $this->profile->pro_pic : null;
    }

    public function getProfileImageAttribute()
    {
        return ($this->profile) ? $this->profile->pro_pic : null;
    }

    public function getAddressAttribute()
    {
        return ($this->profile) ? $this->profile->address : null;
    }

    public function getDobAttribute()
    {
        return ($this->profile) ? $this->profile->dob : null;
    }

    public function getDateOfBirthAttribute()
    {
        return ($this->profile) ? $this->profile->dob : null;
    }

    public function getGenderAttribute()
    {
        return ($this->profile) ? $this->profile->gender : null;
    }
}