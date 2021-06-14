<?php namespace Sheba\Pos\Customer;

use App\Models\Partner;
use App\Models\PartnerPosCustomer;
use App\Models\PosCustomer;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Image;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use App\Models\Profile;
use Sheba\Pos\Repositories\PartnerPosCustomerRepository;
use Sheba\Pos\Repositories\PosCustomerRepository;
use Sheba\Repositories\ProfileRepository;
use Sheba\RequestIdentification;
use Sheba\Reward\ActionRewardDispatcher;

class Creator {
    use FileManager, CdnFileManager;

    private $data;
    /** @var ProfileRepository $profiles */
    private $profiles;
    /** @var PartnerPosCustomerRepository $partnerPosCustomers */
    private $partnerPosCustomers;
    /** @var PosCustomerRepository $posCustomers */
    private $posCustomers;
    /** @var Profile */
    private $profile;
    /** @var Partner */
    private $partner;

    public function __construct(ProfileRepository $profile_repo, PartnerPosCustomerRepository $customer_repo, PosCustomerRepository $pos_customer_repo) {
        $this->profiles            = $profile_repo;
        $this->partnerPosCustomers = $customer_repo;
        $this->posCustomers        = $pos_customer_repo;
    }

    public function setData($data) {
        $this->data = $data;
        return $this;
    }

    public function setProfile(Profile $profile) {
        $this->profile = $profile;
        return $this;
    }

    public function setPartner(Partner $partner) {
        $this->partner = $partner;
        return $this;
    }

    public function hasError() {
        if ($error = $this->alreadyExistError()) {
            return [
                'code'  => 421,
                'msg'   => array_values($error)[0],
                'input' => array_keys($error)[0]
            ];
        }
        return false;
    }

    private function alreadyExistError() {
        $mobile_profile = $this->profiles->checkExistingMobile($this->data['mobile']);
        if ($mobile_profile && $mobile_profile->posCustomer) {
            if (PartnerPosCustomer::where('customer_id', $mobile_profile->posCustomer->id)->where('partner_id', $this->data['partner']->id)->exists())
                return ['mobile' => 'নাম্বারটি ইতোমধ্যে কন্টাক্ট লিস্টে আছে!'];
        };
        if (isset($this->data['email']) && !empty($this->data['email'])) {
            $email_profile = $this->profiles->checkExistingEmail($this->data['email']);
            if ($email_profile && $email_profile->posCustomer) {
                if (PartnerPosCustomer::where('customer_id', $email_profile->posCustomer->id)->where('partner_id', $this->data['partner']->id)->exists())
                    return ['email' => 'Email already exists'];
            }
        }

        return false;
    }

    /**
     * @return PartnerPosCustomer
     */
    public function create() {
        $this->saveImages();
        $this->format();
        $this->data['profile_id'] = $this->resolveProfileId();
        $customer                 = $this->createPosCustomer();
        $this->data['partner_id'] = $this->partner ? $this->partner->id : $this->data['partner']->id;
        $this->data['nick_name'] = $this->data['name'];
        $this->data               = array_except($this->data, ['mobile', 'name', 'email', 'address', 'profile_image', 'partner', 'manager_resource', 'profile_id']);
        $partner_pos_customer     = $this->partnerPosCustomers->where('partner_id', $this->data['partner_id'])->where('customer_id', $customer->id)->first();
        if (!$partner_pos_customer) {
            $partner_pos_customer = $this->partnerPosCustomers->save($this->data + (new RequestIdentification())->get());
            app()->make(ActionRewardDispatcher::class)->run('pos_customer_create', $this->partner, $this->partner, $partner_pos_customer);
        }

        return $partner_pos_customer;
    }

    public function createFromProfile($profile) {
        $this->setProfile(Profile::find($profile));
        $customer             = $this->createPosCustomer();
        $partner_pos_customer = $this->partnerPosCustomers->where('partner_id', $this->partner->id)->where('customer_id', $customer->id)->first();
        if (!$partner_pos_customer) {
            $this->partnerPosCustomers->save($this->data + (new RequestIdentification())->get());
        }
        return $customer;
    }

    private function saveImages() {
        if (!$this->profile && $this->hasFile('profile_image')) $this->data['profile_image'] = $this->saveProfileImage();
    }

    /**
     * Save profile image for resource
     *
     * @return string
     */
    private function saveProfileImage() {
        list($avatar, $avatar_filename) = $this->makeThumb($this->data['profile_image'], $this->data['name']);
        return $this->saveImageToCDN($avatar, getResourceAvatarFolder(), $avatar_filename);
    }

    private function resolveProfileId() {
        if (!$this->profile) {
            $profile = $this->profiles->checkExistingProfile($this->data['mobile'], isset($this->data['email']) ? $this->data['email'] : null);
            if (!($profile instanceof Profile)) $profile = $this->profiles->store($this->data);
            $this->setProfile($profile);
        }
        return $this->profile->id;
    }

    private function createPosCustomer() {
        $customer_query = PosCustomer::where('profile_id', $this->profile->id);
        if ($customer_query->exists()) {
            $customer = $customer_query->first();
        } else
            $customer = $this->posCustomers->save(['profile_id' => $this->profile->id]);

        $this->data['customer_id'] = $customer->id;
        return $customer;
    }

    private function format() {
        $this->data['mobile'] = $this->profile ? $this->profile->mobile : formatMobileAux($this->data['mobile']);
        $this->data['email']  = (isset($this->data['email']) && !empty($this->data['email'])) ? $this->data['email'] : null;
        $this->data['note']   = isset($this->data['note']) ? $this->data['note'] : null;
        $this->data['is_supplier']   = isset($this->data['is_supplier']) ? (int) $this->data['is_supplier'] : 0;
    }

    /**
     * @param $filename
     * @return bool
     */
    private function hasFile($filename) {
        return array_key_exists($filename, $this->data) && ($this->data[$filename] instanceof Image || ($this->data[$filename] instanceof UploadedFile && $this->data[$filename]->getPath() != ''));
    }
}
