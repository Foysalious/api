<?php namespace Sheba\Repositories;

use App\Helper\BangladeshiMobileValidator;
use App\Models\Profile;

class ProfileRepository extends BaseRepository
{
    /**
     * Store a resource profile.
     *
     * @param $data
     * @return Profile
     */
    public function store($data)
    {
        $profile_data = $this->profileDataFormat($data);
        return Profile::create($this->withBothModificationFields($profile_data));
    }

    /**
     * Update a resource profile.
     *
     * @param Profile $profile
     * @param $data
     * @return Profile
     */
    public function update(Profile $profile, $data)
    {
        $profile_data = $this->profileDataFormat($data);
        return $profile->update($this->withUpdateModificationField($profile_data));
    }

    /**
     * Checking existing profile.
     *
     * @param $mobile
     * @param $email
     * @return Profile
     */
    public function checkExistingProfile($mobile, $email)
    {
        $mobile = $mobile ? formatMobile($mobile) : null;
        $mobile = BangladeshiMobileValidator::validate($mobile) ? $mobile : null;
        $email = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;

        $profile = Profile::query();
        if (!$mobile && !$email) {
            return ['code' => 400];
        } elseif ($mobile && $email) {
            $profile = $profile->where('mobile', $mobile)->orWhere('email', $email);
        } elseif ($mobile && !$email) {
            $profile = $profile->where('mobile', $mobile);
        } elseif (!$mobile && $email) {
            $profile = $profile->where('email', $email);
        }

        return $profile->first();

        //$contact_no = formatMobileAux($contact_no);
        //return Profile::where('mobile', $contact_no)->first();
    }


    /**
     * Checking existing profile mobile.
     *
     * @param $mobile
     * @return Profile
     */
    public function checkExistingMobile($mobile)
    {
        $mobile = $mobile ? formatMobileAux($mobile) : null;
        $mobile = BangladeshiMobileValidator::validate($mobile) ? $mobile : null;
        if (!$mobile) return null;
        return Profile::where('mobile', $mobile)->first();
    }


    /**
     * Checking existing profile email.
     *
     * @param $email
     * @return Profile
     */
    public function checkExistingEmail($email)
    {
        $email = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
        if (!$email) return null;
        return Profile::where('email', $email)->first();
    }

    /**
     * Formatting Data for profile table.
     *
     * @param $data
     * @return Array
     */
    private function profileDataFormat($data)
    {
        $profile_data = $data;
        if (isset($data['profile_image'])) {
            $profile_data['pro_pic'] = $data['profile_image'];
        }

        if (isset($data['_token'])) {
            $profile_data['remember_token'] = $data['_token'];

        } else {
            $profile_data['name'] = isset($data['resource_name']) ? $data['resource_name'] : $data['name'];
            $profile_data['remember_token'] = str_random(255);
        }

        return $profile_data;
    }
}