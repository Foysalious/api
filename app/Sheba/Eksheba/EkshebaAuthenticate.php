<?php
/**
 * Created by PhpStorm.
 * User: Tech Land
 * Date: 12/3/2018
 * Time: 6:01 PM
 */

namespace App\Sheba\Eksheba;

use App\Http\Requests\BondhuOrderRequest;
use App\Repositories\affiliateRepository;
use Illuminate\Http\Request;
use App\Models\Affiliate;
use App\Models\Affiliation;
use App\Models\Profile;
use App\Models\Service;
use App\Sheba\Checkout\Checkout;

class EkshebaAuthenticate
{
    private $profile, $mobile;
    public $order, $affiliate, $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setaffiliate()
    {
        $this->profile = Profile::where('mobile', $this->mobile)->first();
        if ($this->profile) {
            $this->affiliate = $this->profile->affiliate;
            if (!$this->affiliate) $this->affiliate = $this->createaffiliate($this->profile);
        } else {
            $this->profile = $this->createProfile();
            $this->affiliate = $this->createaffiliate($this->profile);
        }
        return $this;
    }

    private function createProfile()
    {
        $profile = new Profile();
        $profile->mobile = $this->mobile;
        $profile->name = $this->name;
        $profile->remember_token = str_random(255);
        $profile->save();
        return $profile;
    }

    private function createaffiliate(Profile $profile)
    {
        $affiliate = new Affiliate();
        $affiliate->profile_id = $profile->id;
        $affiliate->remember_token = str_random(255);
        $affiliate->save();
        return $affiliate;
    }

    public function getaffiliate() {
        return $this->affiliate;
    }
}