<?php namespace Sheba\Pos\Customer;

use App\Models\PartnerPosCustomer;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Image;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use App\Models\Profile;
use Sheba\Pos\Repositories\PartnerPosCustomerRepository;
use Sheba\Pos\Repositories\PosCustomerRepository;
use Sheba\Repositories\ProfileRepository;

class Creator
{
    use FileManager, CdnFileManager;

    private $data;

    private $profiles;
    private $partner_pos_customers;
    private $pos_customers;

    public function __construct(ProfileRepository $profile_repo, PartnerPosCustomerRepository $customer_repo, PosCustomerRepository $pos_customer_repo)
    {
        $this->profiles = $profile_repo;
        $this->partner_pos_customers = $customer_repo;
        $this->pos_customers = $pos_customer_repo;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function hasError()
    {
        if ($error = $this->alreadyExistError()) {
            return [
                'code' => 421,
                'msg' => array_values($error)[0],
                'input' => array_keys($error)[0]
            ];
        }
        return false;
    }

    private function alreadyExistError()
    {
        $mobile_profile = $this->profiles->checkExistingMobile($this->data['mobile']);
        if ($mobile_profile && $mobile_profile->posCustomer) return ['mobile' => 'Mobile already exists'];
        return false;
    }

    /**
     * @return PartnerPosCustomer
     */
    public function create()
    {
        $this->saveImages();
        $this->data['mobile'] = formatMobileAux($this->data['mobile']);
        $this->format();
        $this->attachProfile();
        $this->createPosCustomer();
        $this->data['partner_id'] = $this->data['partner']->id;
        $this->data = array_except($this->data, ['mobile', 'name', 'email', 'address', 'profile_image','partner','manager_resource','profile_id']);
        return $this->partner_pos_customers->save($this->data);
    }

    private function saveImages()
    {
        if ($this->hasFile('profile_image')) $this->data['profile_image'] = $this->saveProfileImage();
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

    private function attachProfile()
    {
        $profile = $this->profiles->checkExistingProfile($this->data['mobile'], isset($this->data['email']) ? $this->data['email'] : null);
        if (!($profile instanceof Profile)) $profile = $this->profiles->store($this->data);
        $this->data['profile_id'] = $profile->id;
    }

    private function createPosCustomer()
    {
        $customer = $this->pos_customers->save(['profile_id' => $this->data['profile_id']]);
        $this->data['customer_id'] = $customer->id;
    }

    private function format()
    {
        $this->data['note'] = isset($this->data['note']) ? $this->data['note'] : null;
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