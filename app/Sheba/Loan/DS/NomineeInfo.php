<?php

namespace Sheba\Loan\DS;

use App\Models\Partner;
use App\Models\Profile;
use Sheba\ModificationFields;

class NomineeInfo
{
    use ReflectionArray, ModificationFields;
    protected $name;
    protected $mobile;
    protected $pro_pic;
    protected $nid_image_front;
    protected $nid_image_back;

    /**
     * @param Partner $partner
     * @return Profile
     * @throws \ReflectionException
     */
    public function create(Partner $partner)
    {
        $this->setModifier($partner);
        $data                    = $this->noNullableArray();
        $data['mobile']          = formatMobile($data['mobile']);
        $profile                 = new Profile($data);
        $profile->remember_token = str_random(255);
        $this->withCreateModificationField($profile);
        $profile->save();
        return $profile;
    }

    public function update() { }
}
