<?php namespace Sheba\Repositories;

use App\Helper\BangladeshiMobileValidator;
use App\Models\Profile;
use Carbon\Carbon;
use FontLib\Table\Type\name;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Sheba\NidInfo\ImageSide;
use Sheba\Repositories\Interfaces\ProfileRepositoryInterface;

class ProfileRepository extends BaseRepository implements ProfileRepositoryInterface
{
    public function __construct(Profile $profile)
    {
        parent::__construct();
        $this->setModel($profile);
    }

    public function findByMobile($mobile)
    {
        return $this->model->where('mobile', formatMobile($mobile));
    }

    /**
     * Store a resource profile.
     *
     * @param $data
     * @return Profile
     */
    public function store(array $data)
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
        dd($profile_data);
        return $profile->update($this->withUpdateModificationField($profile_data));
    }

    /**
     * @param Model $profile
     * @param array $data
     * @return bool|Model|int
     */
    public function updateRaw(Model $profile, array $data)
    {
        return $profile->update($this->withUpdateModificationField($data));
    }

    public function updatePassword(Model $profile, $password)
    {
        return $this->updateRaw($profile, ['password' => bcrypt($password)]);
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
        $nid_no = isset($data['nid_no']) ? $data['nid_no'] : null;
        $eProfile = $this->checkExistingEmail($email);
        $mProfile = $this->checkExistingMobile($mobile);
        $nProfile = $this->checkExistingNid($nid_no);
        if ($eProfile && $eProfile->id != $profile->id) return 'email';
        if ($mProfile && $mProfile->id != $profile->id) return 'phone';
        if ($nProfile && $nProfile->id != $profile->id) return 'nid_no';
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

    public function checkExistingNid($nid_no)
    {
        if (!$nid_no) return null;
        return Profile::where('nid_no', $nid_no)->first();
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

        if (isset($data['profile_image']) && !isset($data['pro_pic'])) {
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
        if (isset($data['nid_no'])) {
            $profile_data['nid_no'] = $data['nid_no'];
        }
        if (isset($data['dob'])) {
            $profile_data['dob'] = $data['dob'];
        }
        if (isset($data['bn_name'])) {
            $profile_data['bn_name'] = $data['bn_name'];
        }
        if (isset($data['father_name'])) {
            $profile_data['father_name'] = $data['father_name'];
        }
        if (isset($data['mother_name'])) {
            $profile_data['mother_name'] = $data['mother_name'];
        }
        if (isset($data['post_code'])) {
            $profile_data['post_code'] = $data['post_code'];
        }
        if (isset($data['post_office'])) {
            $profile_data['post_office'] = $data['post_office'];
        }
        if (isset($data['address'])) {
            $profile_data['address'] = $data['address'];
        }
        if (isset($data['permanent_address'])) {
            $profile_data['permanent_address'] = $data['permanent_address'];
        }
        if (isset($data['blood_group'])) {
            $profile_data['blood_group'] = $data['blood_group'];
        }
        if (isset($data['gender'])) {
            $profile_data['gender'] = $data['gender'];
        }
        if (isset($data['nid_image_front'])) {
            $image = $data['nid_image_front'];
            if ($image instanceof UploadedFile) {
                $name = $image->getClientOriginalName() . '_' . ImageSide::FRONT;
                $image = $this->_saveNIdImage($image, $name);
            }
            $profile_data['nid_image_front'] = $image;
        }
        if (isset($data['nid_image_back'])) {
            $image = $data['nid_image_back'];
            if ($image instanceof UploadedFile) {
                $name = $image->getClientOriginalName() . '_' . ImageSide::BACK;
                $image = $this->_saveNIdImage($image, $name);
            }
            $profile_data['nid_image_back'] = $image;
        }
        if (isset($data['passport_image'])) {
            $profile_data['passport_image'] = $data['passport_image'];
        }

        return $profile_data;
    }

    /**
     * @param $file
     * @param $name
     * @return string
     */
    public function saveProPic($file, $name)
    {
        list($file, $filename) = $this->makeProPic($file, $name);
        return $this->saveFileToCDN($file, getProfileAvatarFolder(), $filename);
    }

    /**
     * @param $nid_image
     * @param $name
     * @return string
     */
    private function _saveNIdImage($nid_image, $name)
    {
        list($nid, $nid_filename) = $this->makeThumb($nid_image, $name);
        return $this->saveImageToCDN($nid, getNIDFolder(), $nid_filename);
    }

    public function increase_verification_request_count($profile)
    {
        $profile->nid_verification_request_count = $profile->nid_verification_request_count + 1 ;
        $profile->last_nid_verification_request_date = Carbon::now();
        $profile->update();
    }
}
