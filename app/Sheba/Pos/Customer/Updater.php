<?php namespace Sheba\Pos\Customer;

use App\Models\Partner;
use App\Models\PartnerPosCustomer;
use App\Models\PosCustomer;
use App\Models\Profile;

use Illuminate\Http\UploadedFile;

use Intervention\Image\Image;

use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;

use Sheba\Pos\Repositories\PartnerPosCustomerRepository;
use Sheba\Pos\Repositories\PosCustomerRepository;

use Sheba\Repositories\ProfileRepository;

class Updater
{
    use FileManager, CdnFileManager;

    private $data;
    /** @var ProfileRepository $profileRepo */
    private $profileRepo;
    /** @var PartnerPosCustomerRepository $partnerPosCustomers */
    private $partnerPosCustomers;
    /** @var PosCustomerRepository $posCustomers */
    private $posCustomers;
    /** @var PosCustomer $posCustomer */
    private $posCustomer;
    /** @var Profile $profile */
    private $profile;
    /** @var Partner */
    private $partner;

    public function __construct(ProfileRepository $profile_repo, PartnerPosCustomerRepository $customer_repo,
                                PosCustomerRepository $pos_customer_repo)
    {
        $this->profileRepo = $profile_repo;
        $this->partnerPosCustomers = $customer_repo;
        $this->posCustomers = $pos_customer_repo;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function setProfile(Profile $profile)
    {
        $this->profile = $profile;
        return $this;
    }

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function hasError()
    {
        if ($error = $this->alreadyExistError())
            return ['code' => 421, 'msg' => array_values($error)[0], 'input' => array_keys($error)[0]];
        return false;
    }

    private function alreadyExistError()
    {
        /** @var Profile $profile */
        $profile = $this->profileRepo->checkExistingProfile($this->data['mobile'], isset($this->data['email']) ? $this->data['email'] : null);
        if ($profile && $profile->id != $this->posCustomer->profile_id) return ['mobile' => 'Profile already exists'];
        if ($profile) $this->setProfile($profile);

        return false;
    }

    /**
     * @return PartnerPosCustomer
     */
    public function update()
    {
        $this->saveImages();
        $this->format();
        $this->data['profile_id'] = $this->resolveProfileId();
        return  $this->createOrUpdatePosCustomer();
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
        return $this->saveImageToCDN($avatar, getProfileAvatarFolder(), $avatar_filename);
    }

    private function resolveProfileId()
    {
        $profile_data = [
//            'name'      => $this->data['name'],
            'mobile'    => $this->data['mobile'],
            'email'     => $this->data['email'],
            'address'   => $this->data['address']
        ];
        if (isset($this->data['profile_image'])) $profile_data += ['pro_pic' => $this->data['profile_image']];
        if (!$this->profile) {
            $profile = $this->profileRepo->store($profile_data);
            $this->setProfile($profile);
        } else {
            unset($profile_data['email']);
            $this->profileRepo->update($this->profile, $profile_data);
        }
        return $this->profile->id;
    }

    private function createOrUpdatePosCustomer()
    {
        $customer_query = PosCustomer::where('profile_id', $this->profile->id);
        if ($customer_query->exists()) {
            $customer = $customer_query->first();
        } else
            $customer = $this->posCustomers->save(['profile_id' => $this->profile->id]);

        $partner_pos_customer = $this->partnerPosCustomers->setModel(new PartnerPosCustomer())
            ->where('customer_id', $customer->id)
            ->where('partner_id', $this->data['partner']->id)
            ->first();

        if (!$partner_pos_customer) {
            $partner_pos_customer_data = [
                'partner_id' => $this->data['partner']->id,
                'customer_id' => $customer->id,
                'note' => $this->data['note'],
                'nick_name' => $this->data['name']
            ];
            $partner_pos_customer = $this->partnerPosCustomers->save($partner_pos_customer_data);
        }

        if (isset($this->data['note']) && !empty($this->data['note']))
            $this->partnerPosCustomers->update($partner_pos_customer, ['note' => $this->data['note']]);

        if (isset($this->data['name']) && !empty($this->data['name'])) {
            $this->partnerPosCustomers->update($partner_pos_customer, ['nick_name' => $this->data['name']]);
        }

        $customer->name = $partner_pos_customer['nick_name'];

        return $customer;
    }

    private function format()
    {
        $this->data['mobile'] = formatMobileAux($this->data['mobile']);
        $this->data['email'] = (isset($this->data['email']) && !empty($this->data['email'])) ? $this->data['email'] : null;
        $this->data['note'] = isset($this->data['note']) ? $this->data['note'] : null;
    }

    /**
     * @param $filename
     * @return bool
     */
    private function hasFile($filename)
    {
        return array_key_exists($filename, $this->data) && ($this->data[$filename] instanceof Image || ($this->data[$filename] instanceof UploadedFile && $this->data[$filename]->getPath() != ''));
    }

    /**
     * @param PosCustomer $customer
     * @return $this
     */
    public function setCustomer(PosCustomer $customer)
    {
        $this->posCustomer = $customer;
        return $this;
    }
}