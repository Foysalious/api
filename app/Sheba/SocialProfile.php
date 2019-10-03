<?php

namespace App\Sheba;


use App\Models\Profile;

class SocialProfile
{
    public $profile = [];
    public $info = [];
    private $profile_columns = ['name', 'email', 'mobile', 'gender'];

    public function __construct(array $info)
    {
        $this->info = $info;
    }

    public function getProfileInfo($social_platform_name = null)
    {
        foreach ($this->profile_columns as $profile_column) {
            $this->setColumn($profile_column);
        }
        $this->profile['pro_pic'] = $this->info['picture'];
        $this->setIsVerified();
        if($social_platform_name!=null){
            $this->setSocialAvatarId($social_platform_name);
        }
        return $this->profile;
    }

    private function setColumn($column_name)
    {
        $this->profile[$column_name] = isset($this->info[$column_name]) ? $this->info[$column_name] : null;
    }

    private function setIsVerified()
    {
        $this->profile['mobile_verified'] = $this->profile['mobile'] != null ? 1 : 0;
        $this->profile['email_verified'] = $this->profile['email'] != null ? 1 : 0;
    }

    private function setSocialAvatarId($social_platform_name)
    {
        if ($social_platform_name == 'facebook') {
            $this->profile['fb_id'] = $this->info['id'];
        } elseif ($social_platform_name == 'google') {
            $this->profile['google_id'] = $this->info['sub'];
        }
    }

    public function updateProfileInformation(Profile $profile)
    {
        if (empty($profile->name)) {
            $profile->name = $this->profile['name'];
        }
        if (empty($profile->google_id)) {
            $profile->google_id = $this->profile['google_id'];
        }
        if (empty($profile->gender)) {
            $profile->gender = $this->profile['gender'];
        }
        if (!$profile->email_verified) {
            $profile->email_verified = 1;
        }
        return $profile;
    }
}