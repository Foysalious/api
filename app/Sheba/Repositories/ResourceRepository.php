<?php namespace Sheba\Repositories;

use App\Models\Profile;
use App\Models\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\Resource as ReSrc;
use App\Models\PartnerResource;
use Sheba\Repositories\Interfaces\ProfileRepositoryInterface;

class ResourceRepository extends BaseRepository
{
    /** @var ProfileRepository */
    protected $profileRepo;

    public function __construct(ProfileRepositoryInterface $profileRepo)
    {
        parent::__construct();
        $this->profileRepo = $profileRepo;
    }

    /**
     * Store a new handyman type (generic) resource.
     *
     * @param Request $request
     */
    public function store(Request $request)
    {
        $data = array_merge($request->all(), $this->_saveImage($request));
        $data['mobile'] = formatMobile($data['mobile']);
        $resource_data = $this->resourceDataFormat($data);
        $profile = $this->profileRepo->checkExistingProfile($request->mobile, $request->email);
        if (!($profile instanceof Profile)) $profile = $this->profileRepo->store($data);
        else $this->profileRepo->update($profile, $data);
        $resource_data['profile_id'] = $profile->id;
        $resource_data['remember_token'] = str_random(255);
        $this->save($resource_data);
    }

    public function save($data)
    {
        return Resource::create($this->withBothModificationFields($data));
    }

    /**
     * RESOURCE DATA STORE IN DATABASE WITH PROPER DATA FORMAT
     *
     * @param $data
     * @param $resource
     * @return array
     */
    private function resourceDataFormat($data, $resource = null)
    {
        return [
            'mother_name' => $data['mother_name'],
            'father_name' => $data['father_name'],
            'spouse_name' => isset($data['spouse_name']) ? $data['spouse_name'] : $data['father_name'],
            'nid_no' => $data['nid_no'],
            'nid_image' => isset($data['nid_image']) ? $data['nid_image'] : ($resource ? $resource->nid_image : null),
            'is_trained' => isset($data['is_trained']) ? $data['is_trained'] : 0
        ];
    }

    /**
     * Store a new handyman type (generic) resource.
     *
     * @param $profile
     * @return ReSrc
     */
    public function storePartnerResource($profile)
    {
        $resource_data = [
            'profile_id' => $profile->id,
            'remember_token' => str_random(255)
        ];
        return Resource::create($this->withBothModificationFields($resource_data));
    }


    /**
     * @param Model $resource
     * @param array $data
     * @return Model|void
     */
    public function update(Model $resource, array $data)
    {
        $resource = (!($resource instanceof ReSrc)) ? ReSrc::find($resource) : $resource;
        $resource_data = $this->resourceDataFormat($data, $resource);
        $resource->update($this->withUpdateModificationField($resource_data));
        $this->profileRepo->update($resource->profile, $data);
    }

    /**
     * Update a specified resource with files.
     *
     * @param ReSrc $resource
     * @param Request $request
     */
    public function updateWithImage(ReSrc $resource, Request $request)
    {
        if ($request->hasFile('profile_image')) {
            $this->_deleteOldProfileImage($resource);
        }
        if ($request->hasFile('nid_image')) {
            $this->_deleteOldNIdImage($resource);
        }
        $requested_pro_pic = $this->_saveImage($request);
        $data = array_merge($request->all(), !empty($requested_pro_pic) ? $requested_pro_pic : array('profile_image' => $resource->profile->pro_pic));
        $this->update($resource, $data);
    }

    public function destroy(ReSrc $resource)
    {
        $this->_deleteOldProfileImage($resource);
        $this->_deleteOldNIdImage($resource);
        $resource->delete();
    }

    /**
     * Save images for resource
     *
     * @param Request $request
     * @return array
     */
    private function _saveImage(Request $request)
    {
        $data = [];
        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $this->_saveProfileImage($request);
        }
        if ($request->hasFile('nid_image')) {
            $data['nid_image'] = $this->_saveNIdImage($request);
        }
        return $data;
    }

    /**
     * Save profile image for resource
     *
     * @param Request $request
     * @return string
     */
    private function _saveProfileImage(Request $request)
    {
        list($avatar, $avatar_filename) = $this->makeThumb($request->file('profile_image'), $request->name);
        return $this->saveImageToCDN($avatar, getResourceAvatarFolder(), $avatar_filename);
    }

    /**
     * Save NID image for resource
     *
     * @param Request $request
     * @return string
     */
    private function _saveNIdImage(Request $request)
    {
        list($nid, $nid_filename) = $this->makeThumb($request->file('nid_image'), $request->name);
        return $this->saveImageToCDN($nid, getResourceNIDFolder(), $nid_filename);
    }

    private function _deleteOldProfileImage(ReSrc $resource)
    {
        if ($resource->profile->pro_pic != getProfileDefaultAvatar()) {
            $old_profile_image = substr($resource->profile->pro_pic, strlen(env('S3_URL')));
            $this->deleteImageFromCDN($old_profile_image);
        }
    }

    private function _deleteOldNIdImage(ReSrc $resource)
    {
        if (!empty($resource->nid_image)) {
            $old_nid_image = substr($resource->nid_image, strlen(env('S3_URL')));
            $this->deleteImageFromCDN($old_nid_image);
        }
    }

    private function updateIsFirstTime(ReSrc $resource)
    {
        $resource->update($this->withUpdateModificationField(['is_first_time' => 0]));
    }

    public function getFirstTimeUserData(Request $request)
    {
        $resource = $request->manager_resource;
        $profile  = $resource->profile;
        $is_first_time = 0;
        if($resource->is_first_time){
            $this->updateIsFirstTime($resource);
            $is_first_time = 1;
            if($profile->affiliate)
                return [
                    "message"         => "ইতোমধ্যে আপনার একটি sBondhu একাউন্ট রয়েছে, অনুগ্রহ করে sBondhu অ্যাপ ওপেন করুন। এখন থেকে আপনার sManager এবং sBondhu একাউন্ট একই আইডি এবং পিন দিয়ে লগ ইন করতে হবে",
                    "affiliate_exist" => 1,
                    "is_first_time"   => $is_first_time,
                ];
            return [
                "message"         => "sManager এ প্রদত্ত তথ্য দিয়ে sBondhu একাউন্ট তৈরি হয়ে যাবে। sBondhu তে লগ ইন করুণ",
                "affiliate_exist" => 0,
                "is_first_time"   => $is_first_time,
            ];
        }
        else{
            return [
                "message" => "Not first time user",
                "is_first_time"   => $is_first_time,
            ];
        }
    }
}
