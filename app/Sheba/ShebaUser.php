<?php namespace Sheba;

use App\Models\Affiliate;
use App\Models\Business;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Sheba\MovieTicket\Commission\Partner;

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

    public function getMobile()
    {
        return ($this->user instanceof Customer || $this->user instanceof Affiliate) ? $this->user->profile->mobile : ($this->user instanceof Partner) ? $this->user->getContactNumber() : $this->user->mobile;
    }

    public function getTopUpPrepaidMaxLimit()
    {
        $topup_prepaid_max_limit = $this->user instanceof Business ? $this->user->topup_prepaid_max_limit : 1000;
        return (double)$topup_prepaid_max_limit;
    }

    public function getEmail()
    {
        return ($this->user instanceof Customer || $this->user instanceof Affiliate) ? $this->user->profile->email : $this->user->email;
    }
}
