<?php namespace Sheba\Resource;

use App\Models\Profile;
use App\Models\Resource;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Image;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\Repositories\ProfileRepository;
use Sheba\Repositories\ResourceRepository;

class ResourceCreator
{
    use FileManager, CdnFileManager;

    private $data;

    private $profiles;
    private $resources;

    public function __construct(ProfileRepository $profile_repo, ResourceRepository $resource_repo)
    {
        $this->profiles = $profile_repo;
        $this->resources = $resource_repo;
    }

    public function setData($data)
    {
        $this->data = $data;
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
        $this->saveImages();
        $this->data['mobile'] = formatMobile($this->data['mobile']);
        $this->format();
        $this->attachProfile();
        $this->data['remember_token'] = str_random(255);
        $this->data = array_except($this->data, ['mobile', 'name', 'email', 'address', 'has_profile', 'profile_image', 'resource_types', 'category_ids']);
        return $this->resources->save($this->data);
    }

    private function saveImages()
    {
        if ($this->hasFile('profile_image')) $this->data['profile_image'] = $this->saveProfileImage();
        if ($this->hasFile('nid_image')) $this->data['nid_image'] = $this->saveNIdImage();
    }

    /**
     * Save profile image for resource
     *
     * @return string
     */
    private function saveProfileImage()
    {
        list($avatar, $avatar_filename) = $this->makeThumb($this->data['profile_image'], $this->data['name']);
        return $this->saveImageToCDN($avatar, getResourceAvatarFolder(), $avatar_filename);
    }

    /**
     * Save NID image for resource
     *
     * @return string
     */
    private function saveNIdImage()
    {
        list($nid, $nid_filename) = $this->makeBanner($this->data['nid_image'], $this->data['name']);
        return $this->saveImageToCDN($nid, getResourceNIDFolder(), $nid_filename);
    }

    private function attachProfile()
    {
        $profile = $this->profiles->checkExistingProfile($this->data['mobile'], isset($this->data['email']) ? $this->data['email'] : null);
        if (!($profile instanceof Profile)) $profile = $this->profiles->store($this->data);
        else $this->profiles->update($profile, $this->data);
        $this->data['profile_id'] = $profile->id;
    }

    private function format()
    {
        $this->data['spouse_name'] = isset($this->data['spouse_name']) ? $this->data['spouse_name'] : null;
        $this->data['nid_no'] = isset($this->data['nid_no']) ? $this->data['nid_no'] : null;
        $this->data['nid_image'] = isset($this->data['nid_image']) ? $this->data['nid_image'] : null;
        $this->data['is_trained'] = isset($this->data['is_trained']) ? $this->data['is_trained'] : 0;
        $this->data['alternate_contact'] = !is_null($this->data['alternate_contact']) ? formatMobile(trim($this->data['alternate_contact'])) : null;
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