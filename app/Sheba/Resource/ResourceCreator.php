<?php namespace Sheba\Resource;

use App\Models\Profile;
use App\Models\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Image;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\Repositories\ProfileRepository;
use Sheba\Repositories\ResourceRepository;
use Sheba\Resource\Creator\ResourceCreateRequest;

class ResourceCreator
{
    use FileManager, CdnFileManager;

    private $data;

    private $profiles;
    private $resources;
    /** @var ResourceCreateRequest */
    private $resourceCreateRequest;

    /**
     * ResourceCreator constructor.
     * @param ProfileRepository $profile_repo
     * @param ResourceRepository $resource_repo
     */
    public function __construct(ProfileRepository $profile_repo, ResourceRepository $resource_repo)
    {
        $this->profiles = $profile_repo;
        $this->resources = $resource_repo;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @param ResourceCreateRequest $resourceCreateRequest
     * @return ResourceCreator
     */
    public function setResourceCreateRequest($resourceCreateRequest)
    {
        $this->resourceCreateRequest = $resourceCreateRequest;
        return $this;
    }

    public function hasError()
    {
        if ($error = $this->hasProfileError()) {
            return [
                'code' => 421,
                'msg' => array_values($error)[0],
                'input' => array_keys($error)[0]
            ];
        }
        return false;
    }

    private function hasProfileError()
    {
        $mobile_profile = $this->profiles->checkExistingMobile($this->data['mobile']);
        if ($mobile_profile && $mobile_profile->resource) return ['mobile' => 'Mobile already exists'];
        if (isset($this->data['email'])) {
            $email_profile = $this->profiles->checkExistingEmail($this->data['email']);
            if ($email_profile && $email_profile->resource) return ['email' => 'Email already exists'];
        }
        return false;
    }

    /**
     * @return Resource
     */
    public function create()
    {
        $this->data['mobile'] = formatMobile($this->data['mobile']);
        $this->data['dob'] = $this->resourceCreateRequest->getBirthDate();

        $this->format();
        $profile = $this->attachProfile();

        $this->data['alternate_contact'] = $this->data['alternate_contact'] ? formatMobile($this->data['alternate_contact']) : null;
        $this->data['spouse_name'] = isset($this->data['spouse_name']) ? $this->data['spouse_name'] : null;
        $this->data['is_trained'] = isset($this->data['is_trained']) ? $this->data['is_trained'] : 0;

        return $this->resources->save([
            "spouse_name" => $this->data['spouse_name'],
            "is_trained" => $this->data['is_trained'],
            "profile_id" => $profile->id,
            "remember_token" => str_random(255),
            "alternate_contact" => $this->data['alternate_contact']
        ]);
    }

    private function formatProfilePicture()
    {
        if ($this->resourceCreateRequest->getProfilePicture()) $this->data['pro_pic'] = $this->saveProfileImage();
    }

    private function formatNidInformation()
    {
        $this->data['nid_no'] = $this->resourceCreateRequest->getNidNo();
        if ($this->resourceCreateRequest->getNidFrontImage()) $this->data['nid_image_front'] = $this->saveNIdImageFront();
        if ($this->resourceCreateRequest->getNidBackImage()) $this->data['nid_image_back'] = $this->saveNIdImageBack();
    }

    /**
     * Save profile image for resource
     *
     * @return string
     */
    private function saveProfileImage()
    {
        list($avatar, $avatar_filename) = $this->makeThumb($this->resourceCreateRequest->getProfilePicture(), $this->data['name']);
        return $this->saveImageToCDN($avatar, getResourceAvatarFolder(), $avatar_filename);
    }

    /**
     * Save NID image for resource
     *
     * @return string
     */
    private function saveNIdImageFront()
    {
        list($nid, $nid_filename) = $this->makeBanner($this->resourceCreateRequest->getNidFrontImage(), $this->data['name']."_nid_front");
        return $this->saveImageToCDN($nid, getResourceNIDFolder(), $nid_filename);
    }

    private function saveNIdImageBack()
    {
        list($nid, $nid_filename) = $this->makeBanner($this->resourceCreateRequest->getNidBackImage(), $this->data['name']."_nid_back");
        return $this->saveImageToCDN($nid, getResourceNIDFolder(), $nid_filename);
    }

    /**
     * @return Profile
     */
    private function attachProfile()
    {
        $profile = $this->profiles->checkExistingProfile($this->data['mobile'], isset($this->data['email']) ? $this->data['email'] : null);

        if (!$profile) $profile = $this->profiles->store($this->data);
        else $this->profiles->update($profile, $this->data);

        $this->data['profile_id'] = $profile->id;

        return $profile;
    }

    private function format()
    {
        $this->formatProfilePicture();
        $this->formatNidInformation();
    }

    private function hasFile($filename)
    {
        return array_key_exists($filename, $this->data)
            && (
                $this->data[$filename] instanceof Image
                || (
                    $this->data[$filename] instanceof UploadedFile
                    && $this->data[$filename]->getPath() != ''
                )
            );
    }
}
