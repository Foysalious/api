<?php namespace Sheba;


use App\Models\Affiliate;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

class ShebaUser
{
    private $user;

    public function setUser(Model $user)
    {
        $this->user = $user;
        return $this;
    }

    public function getName()
    {
        return ($this->user instanceof Customer || $this->user instanceof Affiliate) ? $this->user->profile->name : $this->user->name;
    }

    public function getImage()
    {
        return ($this->user instanceof Customer || $this->user instanceof Affiliate) ? $this->user->profile->pro_pic : $this->user->logo;
    }

    public function getWallet()
    {
        return (double)$this->user->wallet;
    }
}