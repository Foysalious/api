<?php namespace Sheba\Loan\DS;

use App\Models\Partner;
use App\Models\Profile;
use Sheba\ModificationFields;

class GranterInfo
{
    use ReflectionArray, ModificationFields;
    protected $name;
    protected $mobile;
    protected $pro_pic;
    protected $nid_no;
    protected $nid_image_back;
    protected $nid_image_front;
    protected $dob;
    protected $address;
    protected $occupation;
    protected $net_worth;

    /**
     * @param Partner $partner
     * @return Profile
     * @throws \ReflectionException
     */
    public function create(Partner $partner)
    {
        $this->setModifier($partner);
        $data = $this->noNullableArray();
        if (isset($data['net_worth'])) {
            unset($data['net_worth']);
        }
        $data['mobile'] = formatMobile($data['mobile']);
        $profile        = Profile::where('mobile', $data['mobile'])->first();
        if (!empty($profile))
            return $profile;
        $profile                 = new Profile($data);
        $profile->remember_token = str_random(255);
        $this->withCreateModificationField($profile);
        $profile->save();
        return $profile;
    }

    protected function update() { }
}
