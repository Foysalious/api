<?php namespace Sheba\Repositories;

use App\Helper\BangladeshiMobileValidator;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

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
     * @param Model $profile
     * @param array $data
     * @return bool|Model|int
     */
    public function update(Model $profile, array $data)
    {
        $profile_data = $this->profileDataFormat($data);
        unset($profile_data['remember_token']);
        return $profile->update($this->withUpdateModificationField($profile_data));
    }

    /**
     * @param $mobile
     * @param $email
     * @return array|Builder|Model|null
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

    public function validate($data, $profile)
    {
        $mobile = isset($data['mobile']) ? $data['mobile'] : null;
        $email = isset($data['email']) ? $data['email'] : null;
        $eProfile = $this->checkExistingEmail($email);
        $mProfile = $this->checkExistingMobile($mobile);
        if ($eProfile && $eProfile->id != $profile->id) return 'email';
        if ($mProfile && $mProfile->id != $profile->id) return 'phone';
        return true;
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
     * @return mixed
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
            $profile_data['name'] = isset($data['resource_name']) ? $data['resource_name'] : isset($data['name']) ? $data['name'] : null;
            if (!$profile_data['name']) unset($profile_data['name']);
            $profile_data['remember_token'] = str_random(255);
        }
        if (isset($data['password'])) {
            $profile_data['password'] = bcrypt($data['password']);
        }
        if (isset($data['mobile'])) {
            $profile_data['mobile'] = formatMobile($data['mobile']);
        }
        if (isset($data['driver_id'])) {
            $profile_data['driver_id'] = $data['driver_id'];
        }

        return $profile_data;
    }
}
